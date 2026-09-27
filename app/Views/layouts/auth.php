<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Login') ?> — LeafDeck</title>

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
            <a href="/" class="inline-flex items-center gap-2 text-2xl font-extrabold text-emerald-600">
                <span class="material-symbols-rounded text-[32px]">menu_book</span>
                LeafDeck
            </a>
            <p class="text-slate-500 text-sm mt-1">Unggah, Tampilkan, Inspirasi.</p>
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
