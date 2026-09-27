<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>

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

    <!-- Top Navigation Toolbar (Google Slides style) -->
    <header class="h-14 bg-slate-900 border-b border-slate-800 flex items-center justify-between px-4 z-30 shrink-0">
        <div class="flex items-center gap-3">
            <a href="/presentation/u/0/" title="Kembali ke Dashboard"
               class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition-colors flex items-center justify-center">
                <span class="material-symbols-rounded text-[22px]">arrow_back</span>
            </a>

            <div class="flex items-center gap-2">
                <span class="material-symbols-rounded text-emerald-500 text-[24px]">menu_book</span>
                <div>
                    <h1 class="text-sm font-bold text-white truncate max-w-xs sm:max-w-md md:max-w-lg" title="<?= esc($deck->title) ?>">
                        <?= esc($deck->title) ?>
                    </h1>
                    <div class="flex items-center gap-2 text-[11px] text-slate-400">
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

    <!-- Main Presentation Stage (Iframe Container) -->
    <main class="flex-1 relative bg-black flex items-center justify-center overflow-hidden">
        <!-- Loading Overlay -->
        <div id="viewer-loading" class="absolute inset-0 bg-slate-900 flex flex-col items-center justify-center z-10 transition-opacity duration-300">
            <div class="w-10 h-10 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin mb-3"></div>
            <p class="text-xs text-slate-400 font-medium">Memuat presentasi...</p>
        </div>

        <iframe id="deck-viewer-frame"
                src="<?= esc($rawUrl) ?>"
                class="w-full h-full border-0 bg-white"
                allow="fullscreen; autoplay"
                allowfullscreen>
        </iframe>
    </main>

    <script src="/assets/js/viewer.js"></script>
</body>
</html>
