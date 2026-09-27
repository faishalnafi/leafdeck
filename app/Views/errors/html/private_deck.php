<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Akses Terbatas — LeafDeck') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
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
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4 font-sans text-slate-800">

    <div class="max-w-md w-full bg-white rounded-2xl border border-slate-200 p-8 text-center shadow-xs">
        <div class="w-16 h-16 bg-amber-50 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-4 border border-amber-200/60">
            <span class="material-symbols-rounded text-[32px]">lock</span>
        </div>

        <h1 class="text-xl font-bold text-slate-900 mb-2">Materi Ini Bersifat Privat</h1>
        <p class="text-sm text-slate-500 mb-6 leading-relaxed">
            Presentasi <strong class="text-slate-700">"<?= esc($deck->title) ?>"</strong> disetel sebagai privat oleh pemiliknya. Hanya guru atau pembuat materi yang memiliki akses ke tautan ini.
        </p>

        <div class="space-y-2">
            <a href="/presentation/u/0/"
               class="w-full inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 px-4 rounded-xl text-xs transition-colors shadow-xs">
                <span class="material-symbols-rounded text-[18px]">dashboard</span>
                <span>Buka Dashboard Saya</span>
            </a>
            <a href="/"
               class="w-full inline-flex items-center justify-center gap-2 border border-slate-200 hover:bg-slate-50 text-slate-600 font-medium py-2.5 px-4 rounded-xl text-xs transition-colors">
                <span>Kembali ke Halaman Utama</span>
            </a>
        </div>
    </div>

</body>
</html>
