<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'LeafDeck') ?> — LeafDeck</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

    <!-- Google Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
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

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/custom.css">

    <?= $this->renderSection('head') ?>
</head>
<body class="bg-slate-50 font-sans text-slate-900 antialiased" data-protected="<?= $protected ?? 'false' ?>">

    <!-- Navbar -->
    <?= $this->include('components/navbar') ?>

    <!-- Main Content -->
    <main class="min-h-screen">
        <?= $this->renderSection('content') ?>
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-200 py-6 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-sm text-slate-500">
                © <?= date('Y') ?> <span class="font-semibold text-emerald-600">LeafDeck</span> — Unggah, Tampilkan, Inspirasi.
            </p>
        </div>
    </footer>

    <!-- Toast Container (injected by app.js) -->

    <!-- JS: Load api.js dulu, baru app.js -->
    <script src="/assets/js/api.js"></script>
    <script src="/assets/js/app.js"></script>
    <?php if (session()->getFlashdata('error')): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                showToast(<?= json_encode(session()->getFlashdata('error')) ?>, 'error');
            });
        </script>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                showToast(<?= json_encode(session()->getFlashdata('success')) ?>, 'success');
            });
        </script>
    <?php endif; ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const p = new URLSearchParams(window.location.search);
            if (p.get('error')) showToast(decodeURIComponent(p.get('error')), 'error');
            if (p.get('success')) showToast(decodeURIComponent(p.get('success')), 'success');
        });
    </script>
    <?= $this->renderSection('scripts') ?>

</body>
</html>

