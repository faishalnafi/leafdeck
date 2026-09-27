<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Google Material Symbols -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        emerald: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full flex flex-col font-sans text-slate-800 antialiased selection:bg-emerald-100 selection:text-emerald-900">

    <div class="max-w-3xl mx-auto px-4 py-8 sm:py-12 w-full flex-1 flex flex-col justify-center">

        <!-- Header Brand -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-emerald-600 text-white shadow-lg shadow-emerald-500/20 mb-3">
                <span class="material-symbols-rounded text-3xl">terminal</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Easy Web Installer</h1>
            <p class="text-xs text-slate-500 mt-1">Pemasangan Otomatis Platform Presentasi & E-Book LeafDeck</p>
        </div>

        <!-- Main Card -->
        <div class="bg-white border border-slate-200/90 rounded-2xl shadow-sm overflow-hidden">
            
            <!-- Banner Status -->
            <div class="p-6 bg-slate-900 text-white flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-emerald-400 flex items-center gap-2">
                        <span class="material-symbols-rounded text-lg">check_circle</span>
                        Wizard Instalasi Sistem 1-Klik
                    </h2>
                    <p class="text-xs text-slate-300 mt-0.5">Memeriksa dependensi, inisialisasi basis data MariaDB, dan pembuatan akun superadmin.</p>
                </div>
                <span class="text-xs font-mono font-semibold px-2.5 py-1 rounded-lg bg-white/10 text-slate-300 border border-white/15">
                    v1.0.0
                </span>
            </div>

            <div class="p-6 sm:p-8 space-y-8">

                <!-- Step 1: System Requirements -->
                <div>
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <span class="flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold">1</span>
                        <span>Pemeriksaan Persyaratan Server</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                        <?php foreach ($requirements as $key => $req): ?>
                            <div class="flex items-center justify-between p-3 rounded-xl border <?= $req['status'] ? 'border-emerald-100 bg-emerald-50/50' : 'border-red-100 bg-red-50/50' ?>">
                                <div>
                                    <p class="font-semibold text-slate-700"><?= esc($req['label']) ?></p>
                                    <p class="text-[11px] text-slate-400"><?= esc($req['current']) ?></p>
                                </div>
                                <?php if ($req['status']): ?>
                                    <span class="material-symbols-rounded text-emerald-600 text-xl">check_circle</span>
                                <?php else: ?>
                                    <span class="material-symbols-rounded text-red-500 text-xl">cancel</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <hr class="border-slate-100">

                <!-- Step 2: Form Configuration -->
                <form id="form-installer" onsubmit="executeInstallation(event)" class="space-y-6">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <span class="flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold">2</span>
                            <span>Konfigurasi Basis Data (MariaDB / MySQL)</span>
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Host Database</label>
                                <input type="text" name="db_host" id="db_host" value="<?= esc($defaultDb['host']) ?>" required
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Port</label>
                                <input type="number" name="db_port" id="db_port" value="<?= esc($defaultDb['port']) ?>" required
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Nama Database</label>
                                <input type="text" name="db_name" id="db_name" value="<?= esc($defaultDb['database']) ?>" required
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono">
                                <p class="text-[10px] text-slate-400 mt-1">Akan dibuat otomatis jika belum tersedia.</p>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Username</label>
                                <input type="text" name="db_user" id="db_user" value="<?= esc($defaultDb['username']) ?>" required
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block font-semibold text-slate-700 mb-1">Password Database</label>
                                <input type="password" name="db_pass" id="db_pass" value="<?= esc($defaultDb['password']) ?>"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono" placeholder="Biarkan kosong jika tanpa password">
                            </div>
                        </div>
                    </div>

                    <hr class="border-slate-100">

                    <!-- Step 3: School & SSO Info -->
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <span class="flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold">3</span>
                            <span>Identitas Instansi & Integrasi SSO</span>
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <div class="sm:col-span-2">
                                <label class="block font-semibold text-slate-700 mb-1">Nama Instansi / Sekolah</label>
                                <input type="text" name="school_name" id="school_name" value="SMAN 3 MJK" required
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">SSO Base URL</label>
                                <input type="url" name="sso_base_url" id="sso_base_url" value="<?= esc($defaultSso['base_url']) ?>" required
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">SSO Client ID</label>
                                <input type="text" name="sso_client_id" id="sso_client_id" value="<?= esc($defaultSso['client_id']) ?>" required
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono">
                            </div>
                        </div>
                    </div>

                    <!-- Alert Box -->
                    <div id="install-alert" class="hidden p-4 rounded-xl text-xs"></div>

                    <!-- Action Button -->
                    <div class="pt-2">
                        <button type="submit" id="btn-submit-install"
                                <?= !$allRequirementsMet ? 'disabled' : '' ?>
                                class="w-full flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-bold py-3.5 px-6 rounded-xl shadow-md shadow-emerald-600/20 hover:shadow-lg transition-all duration-200 text-sm cursor-pointer">
                            <span class="material-symbols-rounded text-xl">play_circle</span>
                            <span>Jalankan Instalasi Otomatis Sekarang</span>
                        </button>
                        <p class="text-center text-[11px] text-slate-400 mt-2">
                            Akan membuat skema database, tabel migrasi, akun superadmin, dan mengonfigurasi berkas .env.
                        </p>
                    </div>
                </form>

            </div>
        </div>

        <p class="text-center text-xs text-slate-400 mt-6">
            &copy; 2026 SMAN 3 MJK &bull; LeafDeck Presentation & E-Book Engine
        </p>

    </div>

    <script>
        async function executeInstallation(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-submit-install');
            const alertBox = document.getElementById('install-alert');
            const originalText = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Sedang Memasang Sistem...</span>
            `;

            alertBox.className = 'hidden';

            const payload = {
                db_host: document.getElementById('db_host').value,
                db_port: document.getElementById('db_port').value,
                db_name: document.getElementById('db_name').value,
                db_user: document.getElementById('db_user').value,
                db_pass: document.getElementById('db_pass').value,
                school_name: document.getElementById('school_name').value,
                sso_base_url: document.getElementById('sso_base_url').value,
                sso_client_id: document.getElementById('sso_client_id').value,
            };

            try {
                const res = await fetch('/install/run', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                });

                const json = await res.json();

                alertBox.classList.remove('hidden');
                if (json.status) {
                    alertBox.className = 'p-4 rounded-xl text-xs bg-emerald-50 border border-emerald-200 text-emerald-900 font-semibold';
                    alertBox.innerHTML = `
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-rounded text-emerald-600 text-lg">check_circle</span>
                            <span>${json.message}</span>
                        </div>
                        <p class="text-[11px] font-normal text-emerald-700 mt-1">Mengalihkan ke halaman beranda dalam 2 detik...</p>
                    `;
                    setTimeout(() => window.location.href = '/', 2000);
                } else {
                    alertBox.className = 'p-4 rounded-xl text-xs bg-red-50 border border-red-200 text-red-900 font-semibold';
                    alertBox.innerHTML = `
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-rounded text-red-600 text-lg">error</span>
                            <span>${json.message}</span>
                        </div>
                    `;
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            } catch (err) {
                console.error(err);
                alertBox.classList.remove('hidden');
                alertBox.className = 'p-4 rounded-xl text-xs bg-red-50 border border-red-200 text-red-900 font-semibold';
                alertBox.innerHTML = 'Terjadi kesalahan komunikasi dengan server.';
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }
    </script>
</body>
</html>
