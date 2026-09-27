<nav class="bg-white border-b border-slate-200 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            <!-- Logo -->
            <a href="/" class="flex items-center gap-2 font-extrabold text-xl text-emerald-600">
                <span class="material-symbols-rounded text-[28px]">menu_book</span>
                LeafDeck
            </a>

            <!-- Nav Links (Desktop) -->
            <div class="hidden md:flex items-center gap-1">
                <a href="/presentation/u/0/"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-all">
                    <span class="material-symbols-rounded text-[18px]">dashboard</span>
                    Dashboard
                </a>
                <a href="/presentation/u/0/trash"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-sm font-medium text-slate-600 hover:bg-red-50 hover:text-red-600 transition-all">
                    <span class="material-symbols-rounded text-[18px]">delete</span>
                    Tong Sampah
                </a>
                <?php if (in_array(session()->get('role'), ['superadmin', 'admin']) || ENVIRONMENT === 'development'): ?>
                    <a href="/admin"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60 hover:bg-emerald-100 transition-all ml-1">
                        <span class="material-symbols-rounded text-[16px]">admin_panel_settings</span>
                        Panel Admin
                    </a>
                <?php endif; ?>
            </div>

            <!-- Right Side -->
            <div class="flex items-center gap-3">
                <div class="hidden md:flex items-center gap-3">
                    <!-- User Info & Role Badge -->
                    <div class="text-right">
                        <p id="nav-user-name" class="text-xs font-bold text-slate-800 leading-tight">
                            <?= esc(session()->get('nama') ?? 'Pengguna') ?>
                        </p>
                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full capitalize">
                            <?= esc(session()->get('role') ?? 'pengguna') ?>
                        </span>
                    </div>
                    <button type="button" onclick="handleLogout()" id="btn-logout"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-red-50 hover:text-red-600 hover:border-red-200 border border-slate-200 transition-all shadow-2xs">
                        <span class="material-symbols-rounded text-[16px]">logout</span>
                        Keluar
                    </button>
                </div>

                <!-- Mobile Menu Button -->
                <button id="btn-mobile-menu" class="md:hidden p-2 rounded-xl hover:bg-slate-100">
                    <span class="material-symbols-rounded text-[24px]">menu</span>
                </button>
            </div>

        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden md:hidden border-t border-slate-100 px-4 py-3 space-y-1">
        <div class="px-3 py-2 mb-2 bg-slate-50 rounded-xl">
            <p class="text-xs font-bold text-slate-800"><?= esc(session()->get('nama') ?? 'Pengguna') ?></p>
            <p class="text-[10px] font-semibold text-emerald-600 capitalize"><?= esc(session()->get('role') ?? 'pengguna') ?></p>
        </div>
        <a href="/presentation/u/0/" class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm text-slate-700 hover:bg-slate-100">
            <span class="material-symbols-rounded text-[18px]">dashboard</span> Dashboard
        </a>
        <a href="/presentation/u/0/trash" class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm text-slate-700 hover:bg-red-50 hover:text-red-600">
            <span class="material-symbols-rounded text-[18px]">delete</span> Tong Sampah
        </a>
        <?php if (in_array(session()->get('role'), ['superadmin', 'admin']) || ENVIRONMENT === 'development'): ?>
            <a href="/admin" class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-bold text-emerald-700 bg-emerald-50">
                <span class="material-symbols-rounded text-[18px]">admin_panel_settings</span> Panel Admin
            </a>
        <?php endif; ?>
        <button type="button" onclick="handleLogout()" id="btn-logout-mobile"
                class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-sm text-red-600 hover:bg-red-50">
            <span class="material-symbols-rounded text-[18px]">logout</span> Keluar
        </button>
    </div>
</nav>

<script>
    // Inisialisasi data & listener navbar
    document.addEventListener('DOMContentLoaded', () => {
        // Mobile menu toggle
        document.getElementById('btn-mobile-menu')?.addEventListener('click', () => {
            document.getElementById('mobile-menu')?.classList.toggle('hidden');
        });
    });
</script>

