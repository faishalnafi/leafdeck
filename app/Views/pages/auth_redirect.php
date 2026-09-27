<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mengalihkan — LeafDeck</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen font-sans">
    <div class="text-center p-8 bg-white border border-slate-200 rounded-2xl shadow-sm max-w-sm w-full mx-4">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-emerald-500 border-t-transparent mb-4"></div>
        <h2 class="text-base font-bold text-slate-800">Menyiapkan Sesi...</h2>
        <p class="text-xs text-slate-500 mt-1">Mengalihkan ke dashboard presentasi</p>
    </div>

    <script src="/assets/js/api.js"></script>
    <script>
        const token = <?= json_encode($token) ?>;
        const user = <?= json_encode($user) ?>;

        if (token) {
            Auth.setToken(token);
        }
        if (user) {
            Auth.setUser(user);
        }

        window.location.href = '/presentation/u/0/';
    </script>
</body>
</html>
