<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Services\FileService;
use App\Services\DeckService;
use App\Models\UserModel;
use RuntimeException;
use ZipArchive;

/**
 * @internal
 */
final class SecurityAndFormatsTest extends CIUnitTestCase
{
    protected FileService $fileService;
    protected DeckService $deckService;
    protected string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fileService = new FileService();
        $this->deckService = new DeckService();
        $this->tempDir     = WRITEPATH . 'temp' . DIRECTORY_SEPARATOR . 'test_sec_' . bin2hex(random_bytes(4));
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $this->fileService->deleteDirectoryRecursive($this->tempDir);
        }
        parent::tearDown();
    }

    /**
     * Test Centralized Local Storage Path
     */
    public function testCentralizedLocalStoragePath(): void
    {
        $path = $this->fileService->getUploadPath();
        $this->assertStringContainsString('uploads' . DIRECTORY_SEPARATOR . 'decks', $path);
        $this->assertTrue(is_dir($path));
    }

    /**
     * Test PDF Upload and Lifecycle
     */
    public function testPdfLifecycle(): void
    {
        $dummyPdf = $this->tempDir . DIRECTORY_SEPARATOR . 'sample.pdf';
        file_put_contents($dummyPdf, "%PDF-1.4\n%test\n1 0 obj<</Type/Catalog>>endobj\n%%EOF");

        $nanoId = 'test_pdf_' . substr(bin2hex(random_bytes(5)), 0, 8);
        $relPath = $this->fileService->savePdf($dummyPdf, $nanoId);

        $this->assertSame('uploads/decks/' . $nanoId . '.pdf', $relPath);
        $this->assertNotNull($this->fileService->readFileContent($relPath));

        $this->assertTrue($this->fileService->delete($relPath));
        $this->assertNull($this->fileService->readFileContent($relPath));
    }

    /**
     * Test PPTX Upload and Lifecycle
     */
    public function testPptxLifecycle(): void
    {
        $dummyPptx = $this->tempDir . DIRECTORY_SEPARATOR . 'presentation.pptx';
        $zip = new ZipArchive();
        $zip->open($dummyPptx, ZipArchive::CREATE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"></Types>');
        $zip->addFromString('ppt/presentation.xml', '<?xml version="1.0"?><p:presentation xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main"></p:presentation>');
        $zip->close();

        $nanoId = 'test_pptx_' . substr(bin2hex(random_bytes(5)), 0, 8);
        $relPath = $this->fileService->savePptx($dummyPptx, $nanoId);

        $this->assertSame('uploads/decks/' . $nanoId . '.pptx', $relPath);
        $this->assertNotNull($this->fileService->readFileContent($relPath));

        $this->assertTrue($this->fileService->delete($relPath));
        $this->assertNull($this->fileService->readFileContent($relPath));
    }

    /**
     * Test DeckService create with PDF
     */
    public function testDeckServiceCreateWithPdf(): void
    {
        $userModel = new UserModel();
        $admin = $userModel->where('role', 'superadmin')->first();

        $dummyPdf = $this->tempDir . DIRECTORY_SEPARATOR . 'Modul_Kimia.pdf';
        file_put_contents($dummyPdf, "%PDF-1.5\nTrailer<<>>\n%%EOF");

        $deck = $this->deckService->create((int) $admin->id, [
            'title'     => 'Modul Kimia PDF',
            'is_public' => 1,
        ], $dummyPdf);

        $this->assertNotNull($deck);
        $this->assertStringEndsWith('.pdf', $deck->file_path);

        // Clean up
        $this->deckService->forceDelete($deck->nano_id, (int) $admin->id);
    }

    /**
     * Test Security: Disallow Unapproved Formats (e.g. .exe, .bat, .sh)
     */
    public function testSecurityRejectDisallowedExtensions(): void
    {
        $exeFile = $this->tempDir . DIRECTORY_SEPARATOR . 'dummy_program.exe';
        file_put_contents($exeFile, "sample binary content");

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Format berkas tidak diizinkan/i');

        $this->fileService->validateUploadedFileSecurely($exeFile);
    }

    /**
     * Test Security: Reject Double Extensions (e.g. exploit.php.pdf)
     */
    public function testSecurityRejectDoubleExtensions(): void
    {
        $doubleExtFile = $this->tempDir . DIRECTORY_SEPARATOR . 'payload.php.pdf';
        file_put_contents($doubleExtFile, "%PDF-1.4\nTest payload");

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Serangan ekstensi ganda terdeteksi/i');

        $this->fileService->validateUploadedFileSecurely($doubleExtFile);
    }

    /**
     * Test Security: Reject Fake PDF (Wrong Magic Bytes)
     */
    public function testSecurityRejectFakePdf(): void
    {
        $fakePdf = $this->tempDir . DIRECTORY_SEPARATOR . 'fake.pdf';
        file_put_contents($fakePdf, "This is not a real PDF file! Plain text spoofing.");

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/magic bytes tidak valid/i');

        $this->fileService->validateUploadedFileSecurely($fakePdf, ['pdf']);
    }

    /**
     * Test Security: Reject PPTX Containing VBA Macro
     */
    public function testSecurityRejectPptxWithMacro(): void
    {
        $macroPptx = $this->tempDir . DIRECTORY_SEPARATOR . 'macro.pptx';
        $zip = new ZipArchive();
        $zip->open($macroPptx, ZipArchive::CREATE);
        $zip->addFromString('[Content_Types].xml', '<Types></Types>');
        $zip->addFromString('ppt/presentation.xml', '<presentation></presentation>');
        $zip->addFromString('ppt/vbaProject.bin', 'macro binary data');
        $zip->close();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Macro VBA/i');

        $this->fileService->validateUploadedFileSecurely($macroPptx, ['pptx']);
    }

    /**
     * Test Security: Reject ZIP Containing Disallowed Script file
     */
    public function testSecurityRejectZipWithScriptFile(): void
    {
        $evilZip = $this->tempDir . DIRECTORY_SEPARATOR . 'bundle.zip';
        $zip = new ZipArchive();
        $zip->open($evilZip, ZipArchive::CREATE);
        $zip->addFromString('index.html', '<h1>Selamat Datang</h1>');
        $zip->addFromString('assets/script.php', 'simple script test');
        $zip->close();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/berkas skrip atau program berbahaya/i');

        $this->fileService->validateUploadedFileSecurely($evilZip, ['zip']);
    }

    /**
     * Test Security: Reject ZIP with Zip-Slip Directory Traversal
     */
    public function testSecurityRejectZipSlipTraversal(): void
    {
        $slipZip = $this->tempDir . DIRECTORY_SEPARATOR . 'slip.zip';
        $zip = new ZipArchive();
        $zip->open($slipZip, ZipArchive::CREATE);
        $zip->addFromString('index.html', '<h1>Hello</h1>');
        $zip->addFromString('../../../etc/passwd.html', 'root test');
        $zip->close();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Zip-Slip/i');

        $this->fileService->validateUploadedFileSecurely($slipZip, ['zip']);
    }

    /**
     * Test Security: Reject HTML with Server-Side Script Tags
     */
    public function testSecurityRejectHtmlWithServerScript(): void
    {
        $phpOpen = '<' . '?php';
        $htmlWithPhp = '<html><body><h1>Judul</h1>' . $phpOpen . ' echo 1; ?></body></html>';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/tag skrip PHP server-side/i');

        $this->fileService->saveHtml($htmlWithPhp, 'test_php_script');
    }
}
