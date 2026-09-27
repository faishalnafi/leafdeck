<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <!-- Top Admin Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-emerald-600 mb-1">
                <span class="material-symbols-rounded text-[16px]">admin_panel_settings</span>
                <span>Pusat Kendali Administrator</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-800 flex items-center gap-2">
                Panel Administrasi
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Kelola seluruh pengguna, moderasi materi presentasi, dan pantau kesehatan sistem LeafDeck.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="/presentation/u/0/"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs">
                <span class="material-symbols-rounded text-[17px] text-emerald-600">dashboard</span>
                Dashboard Guru
            </a>
            <a href="/presentation/u/0/trash"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-red-50 hover:text-red-600 transition-colors shadow-2xs">
                <span class="material-symbols-rounded text-[17px] text-red-500">delete</span>
                Tong Sampah
            </a>
        </div>
    </div>

    <!-- 5 Metric Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
        <!-- Metric 1: Total Pengguna -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">Pengguna</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <span class="material-symbols-rounded text-[18px]">group</span>
                </div>
            </div>
            <p class="text-2xl font-black text-slate-800 mt-2"><?= esc($stats['total_users']) ?></p>
            <span class="inline-block text-[11px] text-emerald-600 font-semibold mt-1">
                <?= esc($stats['active_users']) ?> aktif
            </span>
        </div>

        <!-- Metric 2: Presentasi Aktif -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">Materi Aktif</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <span class="material-symbols-rounded text-[18px]">slideshow</span>
                </div>
            </div>
            <p class="text-2xl font-black text-slate-800 mt-2"><?= esc($stats['total_decks']) ?></p>
            <span class="inline-block text-[11px] text-slate-400 mt-1">Total publik & privat</span>
        </div>

        <!-- Metric 3: Tong Sampah -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">Di Tong Sampah</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <span class="material-symbols-rounded text-[18px]">delete_sweep</span>
                </div>
            </div>
            <p class="text-2xl font-black text-slate-800 mt-2"><?= esc($stats['trash_decks']) ?></p>
            <span class="inline-block text-[11px] text-amber-600 font-medium mt-1">Soft deleted</span>
        </div>

        <!-- Metric 4: Total Tayangan -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">Total Tayangan</span>
                <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <span class="material-symbols-rounded text-[18px]">visibility</span>
                </div>
            </div>
            <p class="text-2xl font-black text-slate-800 mt-2"><?= esc(number_format($stats['total_views'], 0, ',', '.')) ?></p>
            <span class="inline-block text-[11px] text-slate-400 mt-1">Akumulasi views</span>
        </div>

        <!-- Metric 5: Ruang Disk -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs col-span-2 sm:col-span-1">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">Ruang Disk</span>
                <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
                    <span class="material-symbols-rounded text-[18px]">hard_drive</span>
                </div>
            </div>
            <p class="text-2xl font-black text-slate-800 mt-2"><?= esc($stats['storage_formatted']) ?></p>
            <span class="inline-block text-[11px] text-slate-400 mt-1">File HTML decks</span>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 mb-6 pb-2 overflow-x-auto">
        <button onclick="switchTab('tab-users')" id="btn-tab-users"
                class="tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-all bg-emerald-600 text-white shadow-xs flex items-center gap-2 shrink-0">
            <span class="material-symbols-rounded text-[16px]">group</span>
            <span>Manajemen Pengguna</span>
        </button>
        <button onclick="switchTab('tab-decks')" id="btn-tab-decks"
                class="tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-all text-slate-600 hover:bg-slate-100 flex items-center gap-2 shrink-0">
            <span class="material-symbols-rounded text-[16px]">view_list</span>
            <span>Moderasi Seluruh Materi (<?= count($decks) ?>)</span>
        </button>
        <?php if ($currentUser['role'] === 'superadmin'): ?>
        <button onclick="switchTab('tab-sso')" id="btn-tab-sso"
                class="tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-all text-slate-600 hover:bg-slate-100 flex items-center gap-2 shrink-0">
            <span class="material-symbols-rounded text-[16px] text-emerald-600">hub</span>
            <span>Integrasi SSO & Google</span>
            <span class="text-[9px] uppercase px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 font-extrabold tracking-wide">Super Admin</span>
        </button>
        <?php endif; ?>
        <button onclick="switchTab('tab-system')" id="btn-tab-system"
                class="tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-all text-slate-600 hover:bg-slate-100 flex items-center gap-2 shrink-0">
            <span class="material-symbols-rounded text-[16px]">settings_system_daydream</span>
            <span>Status Sistem</span>
        </button>
    </div>

    <!-- Tab 1: Manajemen Pengguna -->
    <div id="tab-users" class="tab-pane">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Daftar Seluruh Pengguna</h3>
                    <p class="text-xs text-slate-400">Total <?= count($users) ?> akun terdaftar di sistem LeafDeck.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/80 text-slate-500 font-semibold uppercase text-[10px] tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3">Pengguna</th>
                            <th class="px-5 py-3">NIP / NIS</th>
                            <th class="px-5 py-3">Role</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Terdaftar</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <?php foreach ($users as $u): ?>
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center shrink-0">
                                            <?= strtoupper(substr($u->nama, 0, 1)) ?>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900"><?= esc($u->nama) ?></p>
                                            <p class="text-[11px] text-slate-400"><?= esc($u->email ?? '-') ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 font-mono text-[11px] text-slate-600">
                                    <?= esc($u->nomor_induk ?? '-') ?>
                                </td>
                                <td class="px-5 py-3.5">
                                    <select onchange="updateUserRole(<?= (int)$u->id ?>, this.value)"
                                            class="bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                        <option value="pengguna" <?= $u->role === 'pengguna' ? 'selected' : '' ?>>Pengguna</option>
                                        <option value="admin" <?= $u->role === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        <option value="superadmin" <?= $u->role === 'superadmin' ? 'selected' : '' ?>>Superadmin</option>
                                    </select>
                                </td>
                                <td class="px-5 py-3.5">
                                    <?php if ((int)$u->is_active === 1): ?>
                                        <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full text-[10px] font-bold border border-emerald-200/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full text-[10px] font-bold border border-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Nonaktif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-3.5 text-slate-400 text-[11px]">
                                    <?= !empty($u->created_at) ? date('d M Y', strtotime($u->created_at)) : '-' ?>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <button onclick="toggleUserStatus(<?= (int)$u->id ?>, '<?= esc($u->nama, 'js') ?>')"
                                            class="px-2.5 py-1 text-[11px] font-semibold rounded-lg border border-slate-200 hover:bg-slate-100 transition-colors text-slate-600">
                                        <?= (int)$u->is_active === 1 ? 'Nonaktifkan' : 'Aktifkan' ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab 2: Moderasi Seluruh Materi -->
    <div id="tab-decks" class="tab-pane hidden">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Semua Presentasi Guru</h3>
                    <p class="text-xs text-slate-400">Moderasi dan pantau seluruh konten presentasi yang diunggah.</p>
                </div>
            </div>

            <?php if (empty($decks)): ?>
                <div class="p-12 text-center text-slate-400 text-xs">
                    Belum ada materi yang diunggah di sistem.
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/80 text-slate-500 font-semibold uppercase text-[10px] tracking-wider border-b border-slate-100">
                            <tr>
                                <th class="px-5 py-3">Materi Presentasi</th>
                                <th class="px-5 py-3">Pengunggah / Guru</th>
                                <th class="px-5 py-3">Visibilitas</th>
                                <th class="px-5 py-3">Tayangan</th>
                                <th class="px-5 py-3">Tanggal Unggah</th>
                                <th class="px-5 py-3 text-right">Aksi Moderasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <?php foreach ($decks as $deck): ?>
                                <tr id="admin-deck-row-<?= esc($deck->nano_id) ?>" class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-5 py-3.5">
                                        <div>
                                            <a href="/presentation/u/0/d/<?= esc($deck->nano_id) ?>/view" target="_blank"
                                               class="font-bold text-slate-900 hover:text-emerald-600 flex items-center gap-1.5 group">
                                                <span><?= esc($deck->title) ?></span>
                                                <span class="material-symbols-rounded text-[14px] text-slate-400 group-hover:text-emerald-600">open_in_new</span>
                                            </a>
                                            <span class="text-[10px] font-mono text-slate-400">ID: <?= esc($deck->nano_id) ?></span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <p class="font-semibold text-slate-800"><?= esc($deck->author_name ?? 'Anonim') ?></p>
                                        <p class="text-[10px] text-slate-400 font-mono"><?= esc($deck->author_nip ?? '-') ?></p>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <?php if ((int)$deck->is_public === 1): ?>
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200/60">
                                                <span class="material-symbols-rounded text-[12px]">public</span> Publik
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-full border border-slate-200">
                                                <span class="material-symbols-rounded text-[12px]">lock</span> Privat
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-3.5 font-bold text-slate-700">
                                        <?= esc($deck->view_count) ?>x
                                    </td>
                                    <td class="px-5 py-3.5 text-[11px] text-slate-400">
                                        <?= date('d M Y, H:i', strtotime($deck->created_at)) ?>
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="/presentation/u/0/d/<?= esc($deck->nano_id) ?>/view" target="_blank"
                                               class="p-1.5 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors"
                                               title="Buka Pratinjau">
                                                <span class="material-symbols-rounded text-[18px]">visibility</span>
                                            </a>
                                            <button onclick="adminForceDeleteDeck('<?= esc($deck->nano_id) ?>', '<?= esc($deck->title, 'js') ?>')"
                                                    class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                    title="Hapus Konten Permanen">
                                                <span class="material-symbols-rounded text-[18px]">delete_forever</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tab 3: Status Sistem -->
    <div id="tab-system" class="tab-pane hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs">
                <h3 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <span class="material-symbols-rounded text-emerald-600">dns</span>
                    Informasi Server & Lingkungan
                </h3>
                <dl class="divide-y divide-slate-100 text-xs">
                    <div class="py-2.5 flex justify-between">
                        <dt class="text-slate-400 font-medium">Framework</dt>
                        <dd class="font-bold text-slate-700">CodeIgniter <?= CodeIgniter\CodeIgniter::CI_VERSION ?></dd>
                    </div>
                    <div class="py-2.5 flex justify-between">
                        <dt class="text-slate-400 font-medium">Versi PHP</dt>
                        <dd class="font-bold text-slate-700"><?= PHP_VERSION ?></dd>
                    </div>
                    <div class="py-2.5 flex justify-between">
                        <dt class="text-slate-400 font-medium">Environment</dt>
                        <dd class="font-bold text-emerald-600 uppercase"><?= ENVIRONMENT ?></dd>
                    </div>
                    <div class="py-2.5 flex justify-between">
                        <dt class="text-slate-400 font-medium">Database</dt>
                        <dd class="font-bold text-slate-700">MariaDB / MySQL (leafdeck_db)</dd>
                    </div>
                    <div class="py-2.5 flex justify-between">
                        <dt class="text-slate-400 font-medium">Zona Waktu</dt>
                        <dd class="font-bold text-slate-700"><?= date_default_timezone_get() ?></dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs">
                <h3 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <span class="material-symbols-rounded text-emerald-600">shield</span>
                    Konfigurasi SSO & Keamanan
                </h3>
                <dl class="divide-y divide-slate-100 text-xs">
                    <div class="py-2.5 flex justify-between">
                        <dt class="text-slate-400 font-medium">SSO Sekolah</dt>
                        <dd class="font-bold text-slate-700"><?= esc(env('leafdeck.schoolName') ?? 'SMAN 3 MJK') ?></dd>
                    </div>
                    <div class="py-2.5 flex justify-between">
                        <dt class="text-slate-400 font-medium">Pola URL</dt>
                        <dd class="font-bold text-slate-700">/presentation/u/0/ (Google-style)</dd>
                    </div>
                    <div class="py-2.5 flex justify-between">
                        <dt class="text-slate-400 font-medium">Metode Hapus</dt>
                        <dd class="font-bold text-slate-700">Soft Delete (Recovery via Tong Sampah)</dd>
                    </div>
                    <div class="py-2.5 flex justify-between">
                        <dt class="text-slate-400 font-medium">Warna Tema Brand</dt>
                        <dd class="font-bold text-emerald-600 flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full bg-[#34A853] inline-block border border-slate-300"></span>
                            #34A853 (Google Green)
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- Tab 4: Integrasi SSO & Google (Superadmin Only) -->
    <?php if ($currentUser['role'] === 'superadmin'): ?>
    <div id="tab-sso" class="tab-pane hidden">
        <div class="space-y-6">

            <!-- Banner Header -->
            <div class="bg-gradient-to-r from-emerald-900 to-slate-900 rounded-2xl p-6 text-white shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-xs flex items-center justify-center shrink-0 border border-white/15">
                        <span class="material-symbols-rounded text-[28px] text-emerald-300">hub</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-lg font-black tracking-tight">Konfigurasi Integrasi SSO & Google OAuth</h2>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-400/20 text-amber-300 border border-amber-400/30 uppercase tracking-wider">
                                Super Administrator
                            </span>
                        </div>
                        <p class="text-xs text-slate-300 mt-1 max-w-2xl leading-relaxed">
                            Pusat kendali koneksi identitas LeafDeck dengan portal Kredensia IdP SMAN 3 MJK (Port 8000). Perubahan parameter di bawah langsung tersimpan secara permanen ke konfigurasi sistem (.env).
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" onclick="testSsoConnection()" id="btn-test-sso"
                            class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs shadow-sm transition-all cursor-pointer">
                        <span class="material-symbols-rounded text-[18px]">network_check</span>
                        <span>Uji Koneksi SSO</span>
                    </button>
                    <a href="<?= esc($ssoConfig['sso_base_url']) ?>" target="_blank"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold text-xs border border-white/15 transition-all">
                        <span class="material-symbols-rounded text-[18px]">open_in_new</span>
                        <span>Portal SSO (Port 8000)</span>
                    </a>
                </div>
            </div>

            <!-- Box Hasil Test Koneksi (Dynamic) -->
            <div id="sso-test-result" class="hidden"></div>

            <form id="form-sso-settings" onsubmit="saveSsoSettings(event)">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <!-- Kolom 1: Konfigurasi SSO Sekolah -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
                        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                                    <span class="material-symbols-rounded text-[18px]">school</span>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-800">SSO Sekolah (Kredensia IdP)</h3>
                                    <p class="text-[11px] text-slate-400">Portal otentikasi tunggal SMAN 3 MJK</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-lg border border-emerald-200/60">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Port 8000
                            </span>
                        </div>

                        <div class="p-5 space-y-4 text-xs">
                            <!-- SSO Base URL -->
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">
                                    SSO Base URL
                                </label>
                                <input type="url" name="sso_base_url" id="input_sso_base_url"
                                       value="<?= esc($ssoConfig['sso_base_url']) ?>" required
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono text-xs text-slate-800">
                                <p class="text-[11px] text-slate-400 mt-1">Alamat root service SSO (contoh: <code class="bg-slate-100 px-1 py-0.5 rounded">http://localhost:8000</code>).</p>
                            </div>

                            <!-- SSO Client ID -->
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">
                                    SSO Client ID (UUID Terdaftar)
                                </label>
                                <input type="text" name="sso_client_id" id="input_sso_client_id"
                                       value="<?= esc($ssoConfig['sso_client_id']) ?>" required
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono text-xs text-slate-800">
                                <p class="text-[11px] text-slate-400 mt-1">ID aplikasi LeafDeck di basis data SSO (<code class="bg-slate-100 px-1 py-0.5 rounded">registered_apps</code>).</p>
                            </div>

                            <!-- SSO JWT Secret -->
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">
                                    SSO JWT Secret (HS256)
                                </label>
                                <div class="relative">
                                    <input type="password" name="sso_jwt_secret" id="input_sso_jwt_secret"
                                           value="<?= esc($ssoConfig['sso_jwt_secret']) ?>" required
                                           class="w-full px-3.5 py-2.5 pr-10 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono text-xs text-slate-800">
                                    <button type="button" onclick="togglePasswordVisibility('input_sso_jwt_secret', 'icon-jwt-secret')"
                                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                                        <span id="icon-jwt-secret" class="material-symbols-rounded text-[18px]">visibility</span>
                                    </button>
                                </div>
                                <p class="text-[11px] text-slate-400 mt-1">Kunci rahasia bersama (secret key) untuk verifikasi tanda tangan token JWT.</p>
                            </div>

                            <!-- SSO API Key -->
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">
                                    SSO Gateway API Key
                                </label>
                                <div class="relative">
                                    <input type="password" name="sso_api_key" id="input_sso_api_key"
                                           value="<?= esc($ssoConfig['sso_api_key']) ?>" required
                                           class="w-full px-3.5 py-2.5 pr-10 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono text-xs text-slate-800">
                                    <button type="button" onclick="togglePasswordVisibility('input_sso_api_key', 'icon-api-key')"
                                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                                        <span id="icon-api-key" class="material-symbols-rounded text-[18px]">visibility</span>
                                    </button>
                                </div>
                                <p class="text-[11px] text-slate-400 mt-1">Kunci resmi LeafDeck di tabel <code class="bg-slate-100 px-1 py-0.5 rounded">kunci_api</code> SSO untuk konsumsi REST API.</p>
                            </div>

                            <!-- SSO Callback URL -->
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">
                                    SSO Callback URL
                                </label>
                                <input type="url" name="sso_callback_url" id="input_sso_callback_url"
                                       value="<?= esc($ssoConfig['sso_callback_url']) ?>" required
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono text-xs text-slate-800">
                                <p class="text-[11px] text-slate-400 mt-1">Tujuan redirect dari SSO setelah autentikasi sukses.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom 2: Google OAuth & Referensi Arsitektur -->
                    <div class="space-y-6">

                        <!-- Card Google OAuth -->
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
                            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24">
                                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-bold text-slate-800">Google OAuth 2.0 (Terpusat)</h3>
                                        <p class="text-[11px] text-slate-400">Akun belajar.id & Google Workspace Sekolah</p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-lg border border-blue-200/60">
                                    Via SSO Gateway
                                </span>
                            </div>

                            <div class="p-5 space-y-4 text-xs">
                                <div class="bg-blue-50/60 border border-blue-100 rounded-xl p-3 text-blue-800 leading-relaxed text-[11px]">
                                    <span class="font-bold">Info Integrasi:</span> LeafDeck terhubung ke akun Google melalui SSO Kredensia (<code class="bg-blue-100/80 px-1 py-0.5 rounded">/auth/google</code>). Pengguna masuk dengan akun Google sekolah dan otomatis terverifikasi tanpa login berulang.
                                </div>

                                <!-- Google Client ID -->
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">
                                        Google Client ID
                                    </label>
                                    <input type="text" name="google_client_id" id="input_google_client_id"
                                           value="<?= esc($ssoConfig['google_client_id']) ?>"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono text-xs text-slate-800">
                                    <p class="text-[11px] text-slate-400 mt-1">Google Client ID yang sama dengan konfigurasi SSO sekolah.</p>
                                </div>

                                <!-- Google Client Secret -->
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">
                                        Google Client Secret
                                    </label>
                                    <div class="relative">
                                        <input type="password" name="google_client_secret" id="input_google_client_secret"
                                               value="<?= esc($ssoConfig['google_client_secret']) ?>"
                                               class="w-full px-3.5 py-2.5 pr-10 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono text-xs text-slate-800">
                                        <button type="button" onclick="togglePasswordVisibility('input_google_client_secret', 'icon-google-secret')"
                                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                                            <span id="icon-google-secret" class="material-symbols-rounded text-[18px]">visibility</span>
                                        </button>
                                    </div>
                                    <p class="text-[11px] text-slate-400 mt-1">Client Secret Google Cloud Console.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Card Blueprint Arsitektur SSO -->
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-5 text-xs">
                            <h4 class="font-bold text-slate-800 mb-3 flex items-center gap-2">
                                <span class="material-symbols-rounded text-emerald-600 text-[18px]">alt_route</span>
                                Referensi Alur & Endpoint Otentikasi
                            </h4>
                            <div class="space-y-2.5 font-mono text-[11px]">
                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200/80">
                                    <span class="text-emerald-700 font-bold">GET</span> <span class="text-slate-800">/sso/login</span>
                                    <p class="text-[10px] text-slate-400 font-sans mt-0.5">Redirect ke portal login SSO sekolah</p>
                                </div>
                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200/80">
                                    <span class="text-blue-700 font-bold">GET</span> <span class="text-slate-800">/sso/google</span>
                                    <p class="text-[10px] text-slate-400 font-sans mt-0.5">Redirect langsung ke login Google via SSO</p>
                                </div>
                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200/80">
                                    <span class="text-purple-700 font-bold">GET</span> <span class="text-slate-800">/sso/callback</span>
                                    <p class="text-[10px] text-slate-400 font-sans mt-0.5">Menerima token JWT & JIT provisioning akun</p>
                                </div>
                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200/80">
                                    <span class="text-red-700 font-bold">GET</span> <span class="text-slate-800">/logout?sso=1</span>
                                    <p class="text-[10px] text-slate-400 font-sans mt-0.5">Keluar dari sesi LeafDeck dan sesi portal SSO</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Footer Save Action -->
                <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4 p-5 bg-white border border-slate-200 rounded-2xl shadow-2xs">
                    <div class="flex items-center gap-2 text-xs text-slate-500">
                        <span class="material-symbols-rounded text-emerald-600 text-[18px]">verified_user</span>
                        <span>Perubahan konfigurasi berlaku seketika untuk semua alur login LeafDeck.</span>
                    </div>

                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <button type="reset"
                                class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold text-xs transition-colors cursor-pointer">
                            Atur Ulang Form
                        </button>
                        <button type="submit" id="btn-save-sso"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm hover:shadow transition-all cursor-pointer">
                            <span class="material-symbols-rounded text-[18px]">save</span>
                            <span>Simpan Konfigurasi SSO</span>
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
    <?php endif; ?>

</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
/**
 * Tab switcher
 */
function switchTab(targetTabId) {
    document.querySelectorAll('.tab-pane').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('bg-emerald-600', 'text-white', 'shadow-xs');
        btn.classList.add('text-slate-600', 'hover:bg-slate-100');
    });

    const pane = document.getElementById(targetTabId);
    if (pane) pane.classList.remove('hidden');

    const btn = document.getElementById(`btn-${targetTabId}`);
    if (btn) {
        btn.classList.add('bg-emerald-600', 'text-white', 'shadow-xs');
        btn.classList.remove('text-slate-600', 'hover:bg-slate-100');
    }
}

/**
 * Update Role Pengguna
 */
async function updateUserRole(userId, newRole) {
    try {
        const res = await fetch(`/api/v1/admin/users/${userId}/role`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ role: newRole })
        });
        const json = await res.json();

        if (json.status) {
            Toast.show(json.message || 'Role berhasil diperbarui', 'success');
        } else {
            Toast.show(json.message || 'Gagal mengubah role', 'error');
        }
    } catch (err) {
        console.error(err);
        Toast.show('Terjadi kesalahan jaringan', 'error');
    }
}

/**
 * Toggle Status Aktif Pengguna
 */
async function toggleUserStatus(userId, userName) {
    if (!confirm(`Ubah status aktif untuk pengguna "${userName}"?`)) return;

    try {
        const res = await fetch(`/api/v1/admin/users/${userId}/status`, {
            method: 'POST'
        });
        const json = await res.json();

        if (json.status) {
            Toast.show(json.message || 'Status pengguna berhasil diubah', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            Toast.show(json.message || 'Gagal mengubah status', 'error');
        }
    } catch (err) {
        console.error(err);
        Toast.show('Terjadi kesalahan jaringan', 'error');
    }
}

/**
 * Hapus Deck Permanen (Moderasi Admin)
 */
async function adminForceDeleteDeck(nanoId, title) {
    if (!confirm(`PERINGATAN ADMIN:\nHapus permanen presentasi "${title}" beserta file HTML fisiknya? Tindakan ini tidak dapat dibatalkan.`)) {
        return;
    }

    try {
        const res = await fetch(`/api/v1/admin/decks/${nanoId}`, {
            method: 'DELETE'
        });
        const json = await res.json();

        if (json.status) {
            Toast.show('Materi presentasi berhasil dihapus permanen oleh Admin', 'success');
            const row = document.getElementById(`admin-deck-row-${nanoId}`);
            if (row) {
                row.style.opacity = '0';
                setTimeout(() => row.remove(), 250);
            }
        } else {
            Toast.show(json.message || 'Gagal menghapus presentasi', 'error');
        }
    } catch (err) {
        console.error(err);
        Toast.show('Terjadi kesalahan jaringan', 'error');
    }
}

/**
 * Toggle visibilitas password/secret input
 */
function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (!input || !icon) return;

    if (input.type === 'password') {
        input.type = 'text';
        icon.textContent = 'visibility_off';
    } else {
        input.type = 'password';
        icon.textContent = 'visibility';
    }
}

/**
 * Simpan Pengaturan Integrasi SSO & Google
 */
async function saveSsoSettings(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-save-sso');
    const originalText = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = `
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>Menyimpan...</span>
    `;

    const data = {
        sso_base_url: document.getElementById('input_sso_base_url')?.value,
        sso_client_id: document.getElementById('input_sso_client_id')?.value,
        sso_jwt_secret: document.getElementById('input_sso_jwt_secret')?.value,
        sso_api_key: document.getElementById('input_sso_api_key')?.value,
        sso_callback_url: document.getElementById('input_sso_callback_url')?.value,
        google_client_id: document.getElementById('input_google_client_id')?.value,
        google_client_secret: document.getElementById('input_google_client_secret')?.value,
    };

    try {
        const res = await fetch('/admin/settings/sso', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        });
        const json = await res.json();

        if (json.status) {
            Toast.show(json.message || 'Pengaturan SSO berhasil disimpan!', 'success');
        } else {
            Toast.show(json.message || 'Gagal menyimpan konfigurasi', 'error');
        }
    } catch (err) {
        console.error(err);
        Toast.show('Terjadi gangguan saat menyimpan ke sistem', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

/**
 * Uji Koneksi Live ke Gateway SSO
 */
async function testSsoConnection() {
    const btn = document.getElementById('btn-test-sso');
    const resultBox = document.getElementById('sso-test-result');
    const originalText = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = `
        <svg class="animate-spin -ml-1 mr-1 h-3.5 w-3.5 text-slate-950" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>Menguji...</span>
    `;

    const data = {
        sso_base_url: document.getElementById('input_sso_base_url')?.value,
        sso_api_key: document.getElementById('input_sso_api_key')?.value,
    };

    try {
        const res = await fetch('/admin/settings/test-sso', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        });
        const json = await res.json();

        resultBox.classList.remove('hidden');

        if (json.status) {
            resultBox.innerHTML = `
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-start gap-3">
                    <span class="material-symbols-rounded text-emerald-600 text-[22px] shrink-0 mt-0.5">check_circle</span>
                    <div class="flex-1 text-xs">
                        <p class="font-bold text-sm text-emerald-950">${json.message}</p>
                        <p class="text-emerald-800 mt-1">Status: HTTP ${json.http_code} &bull; Latensi: ${json.latency_ms} ms &bull; Server Time: ${json.data?.data?.server_time || '-'}</p>
                    </div>
                </div>
            `;
            Toast.show('Koneksi SSO terverifikasi sukses!', 'success');
        } else {
            resultBox.innerHTML = `
                <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-900 flex items-start gap-3">
                    <span class="material-symbols-rounded text-red-600 text-[22px] shrink-0 mt-0.5">error</span>
                    <div class="flex-1 text-xs">
                        <p class="font-bold text-sm text-red-950">Gagal Terhubung ke SSO Sekolah</p>
                        <p class="text-red-800 mt-1">${json.message}</p>
                        <p class="text-red-700/80 text-[11px] mt-1">Petunjuk: Pastikan aplikasi SSO di direktori <code>D:\\Server\\WebApp\\sso</code> berjalan di port 8000 (<code>php artisan serve --port=8000</code>).</p>
                    </div>
                </div>
            `;
            Toast.show('Uji koneksi SSO gagal: ' + json.message, 'error');
        }
    } catch (err) {
        console.error(err);
        resultBox.classList.remove('hidden');
        resultBox.innerHTML = `
            <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-900 flex items-start gap-3">
                <span class="material-symbols-rounded text-red-600 text-[22px] shrink-0 mt-0.5">wifi_off</span>
                <div class="flex-1 text-xs">
                    <p class="font-bold text-sm text-red-950">Terjadi Kesalahan Jaringan</p>
                    <p class="text-red-800 mt-1">Tidak dapat mengirim permintaan ke server LeafDeck.</p>
                </div>
            </div>
        `;
        Toast.show('Gagal melakukan uji koneksi', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}
</script>
<?= $this->endSection() ?>
