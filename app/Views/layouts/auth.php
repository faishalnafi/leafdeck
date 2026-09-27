<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Login') ?> — LeafDeck</title>

    <!-- Favicon & Icons -->
    <link rel="icon" type="image/png" href="https://support.nafii.my.id/icon/domains.png">
    <link rel="shortcut icon" type="image/png" href="https://support.nafii.my.id/icon/domains.png">
    <link rel="apple-touch-icon" href="https://support.nafii.my.id/icon/domains.png">

    <!-- Thumbnail Meta Tags (Open Graph & Twitter) -->
    <meta property="og:title" content="<?= esc($title ?? 'Masuk') ?> — LeafDeck">
    <meta property="og:description" content="Platform Presentasi & E-Book Interaktif SMAN 3 MJK">
    <meta property="og:image" content="https://support.nafii.my.id/icon/domains.png">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?= esc($title ?? 'Masuk') ?> — LeafDeck">
    <meta name="twitter:image" content="https://support.nafii.my.id/icon/domains.png">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Google Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: {
                        emerald: {
                            50:  '#eaf7ee',
                            100: '#d1f0db',
                            200: '#a7e2bc',
                            500: '#34A853',
                            600: '#34A853',
                            700: '#2d9249',
                            800: '#237339',
                        },
                        brand: {
                            DEFAULT: '#34A853',
                            50:  '#eaf7ee',
                            100: '#d1f0db',
                            500: '#34A853',
                            600: '#34A853',
                            700: '#2d9249',
                        }
                    }
                }
            }
        }
    </script>

    <link rel="stylesheet" href="/assets/css/custom.css">
</head>
<body class="bg-gradient-to-br from-emerald-50 via-white to-slate-100 font-sans min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">

        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center gap-2.5 text-2xl font-black text-slate-800 hover:opacity-90 transition-opacity">
                <img src="https://support.nafii.my.id/icon/domains.png" alt="LeafDeck" class="w-9 h-9 rounded-xl object-contain shadow-2xs">
                <span>LeafDeck</span>
            </a>
            <p class="text-slate-500 text-xs mt-1">Platform Presentasi & E-Book Edukasi</p>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
            <?= $this->renderSection('content') ?>
        </div>

    </div>

    <!-- Toast Container -->

    <script src="/assets/js/api.js"></script>
    <script src="/assets/js/app.js"></script>
    <?= $this->renderSection('scripts') ?>

</body>
</html>
