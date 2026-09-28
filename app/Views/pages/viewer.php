<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>

    <!-- Favicon & Icons -->
    <link rel="icon" type="image/png" href="https://support.nafii.my.id/icon/domains.png">
    <link rel="shortcut icon" type="image/png" href="https://support.nafii.my.id/icon/domains.png">
    <link rel="apple-touch-icon" href="https://support.nafii.my.id/icon/domains.png">

    <!-- Thumbnail Meta Tags (Open Graph & Twitter) -->
    <meta property="og:title" content="<?= esc($title) ?>">
    <meta property="og:description" content="Presentasi & E-Book Interaktif LeafDeck">
    <meta property="og:image" content="https://support.nafii.my.id/icon/domains.png">
    <meta property="og:type" content="article">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?= esc($title) ?>">
    <meta name="twitter:description" content="Presentasi & E-Book Interaktif LeafDeck">
    <meta name="twitter:image" content="https://support.nafii.my.id/icon/domains.png">

    <!-- Google Fonts & Material Symbols -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

    <!-- Tailwind CSS CDN with Google Green -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: {
                        brand: { DEFAULT: '#34A853', 600: '#34A853', 700: '#2d9249' }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="/assets/css/custom.css">
</head>
<body class="bg-slate-900 text-slate-100 h-screen flex flex-col overflow-hidden font-sans select-none">

    <?php
    $formatInfo = match ($fileType ?? 'html') {
        'pdf'  => ['icon' => 'picture_as_pdf', 'color' => 'text-red-400', 'badge' => 'Dokumen PDF', 'badge_bg' => 'bg-red-500/20 text-red-300 border-red-500/30'],
        'pptx' => ['icon' => 'co_present',    'color' => 'text-amber-400', 'badge' => 'PowerPoint (PPTX)', 'badge_bg' => 'bg-amber-500/20 text-amber-300 border-amber-500/30'],
        'zip'  => ['icon' => 'folder_zip',    'color' => 'text-blue-400', 'badge' => 'Flipbook Interaktif', 'badge_bg' => 'bg-blue-500/20 text-blue-300 border-blue-500/30'],
        default=> ['icon' => 'menu_book',     'color' => 'text-emerald-400', 'badge' => 'Slide HTML5', 'badge_bg' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30'],
    };
    ?>

    <!-- Top Navigation Toolbar (Google Slides style) -->
    <header class="h-14 bg-slate-900 border-b border-slate-800 flex items-center justify-between px-4 z-30 shrink-0">
        <div class="flex items-center gap-3">
            <a href="/presentation/u/0/" title="Kembali ke Dashboard"
               class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition-colors flex items-center justify-center">
                <span class="material-symbols-rounded text-[22px]">arrow_back</span>
            </a>

            <div class="flex items-center gap-2.5">
                <span class="material-symbols-rounded <?= $formatInfo['color'] ?> text-[24px]"><?= $formatInfo['icon'] ?></span>
                <div>
                    <h1 class="text-sm font-bold text-white truncate max-w-xs sm:max-w-md md:max-w-lg" title="<?= esc($deck->title) ?>">
                        <?= esc($deck->title) ?>
                    </h1>
                    <div class="flex items-center gap-2 text-[11px] text-slate-400">
                        <span class="px-1.5 py-0.2 rounded border text-[10px] font-bold <?= $formatInfo['badge_bg'] ?>">
                            <?= $formatInfo['badge'] ?>
                        </span>
                        <span>Ditayangkan <?= (int) $deck->view_count ?> kali</span>
                        <?php if ($deck->is_public): ?>
                            <span class="text-emerald-400">• Publik</span>
                        <?php else: ?>
                            <span class="text-slate-500">• Privat</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Action Controls -->
        <div class="flex items-center gap-1.5">
            <?php if (!empty($downloadUrl)): ?>
            <a href="<?= esc($downloadUrl) ?>" title="Unduh Berkas Asli"
               class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition-colors flex items-center justify-center">
                <span class="material-symbols-rounded text-[20px]">download</span>
            </a>
            <?php endif; ?>

            <a href="<?= esc($rawUrl) ?>" target="_blank" title="Buka di Tab Baru"
               class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition-colors flex items-center justify-center">
                <span class="material-symbols-rounded text-[20px]">open_in_new</span>
            </a>

            <button id="btn-fullscreen-toggle" title="Layar Penuh"
                    class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition-colors flex items-center justify-center">
                <span class="material-symbols-rounded text-[20px]">fullscreen</span>
            </button>
        </div>
    </header>

    <!-- Main Presentation Stage (Iframe Container / Presentation Player) -->
    <main class="flex-1 relative bg-black flex items-center justify-center overflow-hidden">
        <!-- Loading Overlay -->
        <div id="viewer-loading" class="absolute inset-0 bg-slate-900 flex flex-col items-center justify-center z-10 transition-opacity duration-300">
            <div class="w-10 h-10 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin mb-3"></div>
            <p class="text-xs text-slate-400 font-medium">Memuat presentasi...</p>
        </div>

        <?php if (($fileType ?? '') === 'pptx'): ?>
            <!-- PPTX Presentation Card Showcase & Viewer -->
            <div class="w-full h-full flex flex-col items-center justify-center p-6 bg-slate-950 text-center">
                <div class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl flex flex-col items-center">
                    <div class="w-20 h-20 bg-amber-500/10 text-amber-400 rounded-3xl flex items-center justify-center mb-5 border border-amber-500/20">
                        <span class="material-symbols-rounded text-[48px]">co_present</span>
                    </div>

                    <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 uppercase tracking-wider mb-2">
                        Microsoft PowerPoint Presentation
                    </span>

                    <h2 class="text-lg font-black text-white mb-2 leading-snug"><?= esc($deck->title) ?></h2>
                    <p class="text-xs text-slate-400 mb-6 leading-relaxed">
                        <?= !empty($deck->description) ? esc($deck->description) : 'Materi presentasi PowerPoint (PPTX) siap ditayangkan atau diunduh untuk presentasi di kelas.' ?>
                    </p>

                    <div class="w-full space-y-3">
                        <a href="<?= esc($downloadUrl) ?>"
                           class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-amber-500 hover:bg-amber-400 active:bg-amber-600 text-slate-950 font-bold text-xs shadow-lg transition-all cursor-pointer">
                            <span class="material-symbols-rounded text-[20px]">download</span>
                            <span>Unduh Berkas PPTX (Offline Presentation)</span>
                        </a>

                        <?php if (str_starts_with($rawUrl, 'https://') && !str_contains($rawUrl, 'localhost')): ?>
                        <a href="https://view.officeapps.live.com/op/embed.aspx?src=<?= urlencode($rawUrl) ?>" target="_blank"
                           class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-xs border border-slate-700 transition-all cursor-pointer">
                            <span class="material-symbols-rounded text-[18px]">slideshow</span>
                            <span>Buka di Microsoft Office Web Viewer</span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <script>
                // Hide loading overlay immediately for PPTX card
                window.addEventListener('DOMContentLoaded', () => {
                    const l = document.getElementById('viewer-loading');
                    if (l) l.classList.add('hidden');
                });
            </script>
        <?php else: ?>
            <!-- HTML, ZIP, and PDF Viewer Frame -->
            <iframe id="deck-viewer-frame"
                    src="<?= esc($rawUrl) ?>"
                    class="w-full h-full border-0 bg-white"
                    allow="fullscreen; autoplay"
                    allowfullscreen>
            </iframe>
        <?php endif; ?>
    </main>

    <script src="/assets/js/viewer.js"></script>
</body>
</html>
