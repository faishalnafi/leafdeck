<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <!-- Top Navigation & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="/presentation/u/0/" class="hover:text-emerald-600 flex items-center gap-1 transition-colors">
                    <span class="material-symbols-rounded text-[16px]">arrow_back</span>
                    Dashboard
                </a>
                <span>/</span>
                <span class="text-slate-700">Tong Sampah</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-800 flex items-center gap-2.5">
                <span class="p-2 bg-red-50 text-red-600 rounded-xl flex items-center justify-center">
                    <span class="material-symbols-rounded text-[24px]">delete</span>
                </span>
                Tong Sampah
            </h1>
        </div>

        <div class="flex items-center gap-2">
            <a href="/presentation/u/0/"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs">
                <span class="material-symbols-rounded text-[18px]">arrow_back</span>
                Kembali ke Beranda
            </a>
        </div>
    </div>

    <!-- Info Banner -->
    <div class="mb-8 bg-amber-50/70 border border-amber-200/80 rounded-2xl p-4 flex items-start gap-3 text-xs text-amber-900 shadow-2xs">
        <span class="material-symbols-rounded text-[20px] text-amber-600 shrink-0 mt-0.5">info</span>
        <div>
            <p class="font-bold">Informasi Soft Delete</p>
            <p class="text-amber-800/90 mt-0.5 leading-relaxed">
                Item di dalam tong sampah telah disembunyikan dari dashboard dan publik. Anda dapat <strong>memulihkan</strong> item ke dashboard kapan saja, atau memilih <strong>hapus permanen</strong> untuk menghapus file fisik HTML dari server selamanya.
            </p>
        </div>
    </div>

    <!-- Trash Container -->
    <div id="trash-container">
        <?php if (empty($decks)): ?>
            <!-- Empty State -->
            <div id="trash-empty-state" class="bg-white rounded-2xl border border-slate-200 p-12 text-center max-w-md mx-auto my-12 shadow-xs">
                <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-rounded text-[32px]">delete_sweep</span>
                </div>
                <h3 class="text-base font-bold text-slate-800">Tong sampah kosong</h3>
                <p class="text-xs text-slate-500 mt-1 mb-6 leading-relaxed">Tidak ada presentasi yang sedang berada di tong sampah.</p>
                <a href="/presentation/u/0/"
                   class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-2.5 rounded-xl text-xs transition-colors shadow-xs">
                    <span class="material-symbols-rounded text-[18px]">folder_open</span>
                    Kembali ke Dashboard
                </a>
            </div>
        <?php else: ?>
            <!-- Trash Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5" id="trash-grid">
                <?php foreach ($decks as $deck): ?>
                    <div id="trash-card-<?= esc($deck->nano_id) ?>"
                         class="bg-white rounded-xl border border-slate-200 hover:border-slate-300 hover:shadow-md transition-all duration-200 flex flex-col justify-between overflow-hidden group">

                        <!-- Preview Area -->
                        <div class="aspect-[16/10] bg-slate-100 relative overflow-hidden flex flex-col items-center justify-center text-slate-400 border-b border-slate-100">
                            <span class="material-symbols-rounded text-[48px] text-slate-300 group-hover:text-red-400 transition-colors">slideshow</span>
                            
                            <!-- Soft-deleted badge -->
                            <span class="absolute top-2.5 right-2.5 bg-red-100 text-red-700 font-bold text-[10px] px-2 py-0.5 rounded-full flex items-center gap-1 border border-red-200/60 shadow-2xs">
                                <span class="material-symbols-rounded text-[12px]">delete</span>
                                Di Tong Sampah
                            </span>
                        </div>

                        <!-- Card Details -->
                        <div class="p-4 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-slate-800 line-clamp-1" title="<?= esc($deck->title) ?>">
                                    <?= esc($deck->title) ?>
                                </h3>

                                <div class="mt-2 space-y-1 text-[11px] text-slate-500">
                                    <div class="flex items-center gap-1.5 text-slate-400">
                                        <span class="material-symbols-rounded text-[14px]">calendar_today</span>
                                        <span>Dibuat: <?= date('d M Y', strtotime($deck->created_at)) ?></span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-red-500/90 font-medium">
                                        <span class="material-symbols-rounded text-[14px]">auto_delete</span>
                                        <span>Dihapus: <?= !empty($deck->deleted_at) ? date('d M Y, H:i', strtotime($deck->deleted_at)) : '-' ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons on Card -->
                            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-2">
                                <!-- Pulihkan / Restore -->
                                <button onclick="restoreDeck('<?= esc($deck->nano_id) ?>', '<?= esc($deck->title, 'js') ?>')"
                                        class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold rounded-lg text-xs transition-colors border border-emerald-200/60">
                                    <span class="material-symbols-rounded text-[16px]">restore_from_trash</span>
                                    Pulihkan
                                </button>

                                <!-- Hapus Permanen / Force Delete -->
                                <button onclick="openForceDeleteModal('<?= esc($deck->nano_id) ?>', '<?= esc($deck->title, 'js') ?>')"
                                        class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors border border-slate-200 hover:border-red-200"
                                        title="Hapus Permanen">
                                    <span class="material-symbols-rounded text-[18px]">delete_forever</span>
                                </button>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Modal Konfirmasi Hapus Permanen -->
<div id="modal-force-delete" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4 animate-in fade-in duration-150">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-100 animate-in zoom-in-95 duration-150">
        <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-4">
            <span class="material-symbols-rounded text-[28px]">delete_forever</span>
        </div>

        <h3 class="text-base font-bold text-slate-900 text-center">Hapus Permanen?</h3>
        <p class="text-xs text-slate-600 text-center mt-2 leading-relaxed">
            Apakah Anda yakin ingin menghapus materi <strong id="force-delete-deck-title" class="text-slate-900"></strong> secara permanen?
        </p>
        <p class="text-[11px] text-red-600 bg-red-50 p-2.5 rounded-xl border border-red-200/60 text-center mt-3 font-medium">
            ⚠️ Tindakan ini tidak dapat dibatalkan. File HTML dan semua data presentasi akan dihapus selamanya dari server.
        </p>

        <div class="mt-6 flex items-center gap-3">
            <button type="button" onclick="closeForceDeleteModal()"
                    class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                Batal
            </button>
            <button type="button" id="btn-confirm-force-delete"
                    class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-semibold shadow-xs transition-colors">
                <span class="material-symbols-rounded text-[16px]">delete_forever</span>
                Hapus Selamanya
            </button>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let targetForceDeleteNanoId = null;

/**
 * Pulihkan presentasi dari tong sampah
 */
async function restoreDeck(nanoId, title) {
    if (!confirm(`Pulihkan presentasi "${title}" kembali ke Dashboard?`)) {
        return;
    }

    try {
        const res = await API.decks.restore(nanoId);
        if (res && res.status) {
            Toast.show(`Presentasi "${title}" berhasil dipulihkan!`, 'success');
            removeCardFromGrid(nanoId);
        } else {
            Toast.show(res?.message || 'Gagal memulihkan presentasi', 'error');
        }
    } catch (err) {
        console.error(err);
        Toast.show('Terjadi kesalahan saat memulihkan presentasi', 'error');
    }
}

/**
 * Buka modal konfirmasi hapus permanen
 */
function openForceDeleteModal(nanoId, title) {
    targetForceDeleteNanoId = nanoId;
    document.getElementById('force-delete-deck-title').textContent = `"${title}"`;
    document.getElementById('modal-force-delete').classList.remove('hidden');
}

/**
 * Tutup modal hapus permanen
 */
function closeForceDeleteModal() {
    targetForceDeleteNanoId = null;
    document.getElementById('modal-force-delete').classList.add('hidden');
}

/**
 * Konfirmasi eksekusi hapus permanen
 */
document.getElementById('btn-confirm-force-delete')?.addEventListener('click', async () => {
    if (!targetForceDeleteNanoId) return;

    const btn = document.getElementById('btn-confirm-force-delete');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<span class="material-symbols-rounded animate-spin text-[16px]">progress_activity</span> Menghapus...`;

    try {
        const res = await API.decks.forceDelete(targetForceDeleteNanoId);
        if (res && res.status) {
            Toast.show('Presentasi berhasil dihapus permanen beserta filenya', 'success');
            removeCardFromGrid(targetForceDeleteNanoId);
            closeForceDeleteModal();
        } else {
            Toast.show(res?.message || 'Gagal menghapus permanen', 'error');
        }
    } catch (err) {
        console.error(err);
        Toast.show('Terjadi kesalahan saat menghapus permanen', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
});

/**
 * Hapus elemen card dari grid dengan animasi
 */
function removeCardFromGrid(nanoId) {
    const card = document.getElementById(`trash-card-${nanoId}`);
    if (card) {
        card.style.transition = 'all 0.3s ease';
        card.style.opacity = '0';
        card.style.transform = 'scale(0.95)';
        setTimeout(() => {
            card.remove();
            checkEmptyTrashState();
        }, 300);
    }
}

/**
 * Tampilkan empty state jika semua item di tong sampah sudah dipulihkan atau dihapus
 */
function checkEmptyTrashState() {
    const grid = document.getElementById('trash-grid');
    if (grid && grid.children.length === 0) {
        const container = document.getElementById('trash-container');
        container.innerHTML = `
            <div id="trash-empty-state" class="bg-white rounded-2xl border border-slate-200 p-12 text-center max-w-md mx-auto my-12 shadow-xs animate-in fade-in duration-200">
                <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-rounded text-[32px]">delete_sweep</span>
                </div>
                <h3 class="text-base font-bold text-slate-800">Tong sampah kosong</h3>
                <p class="text-xs text-slate-500 mt-1 mb-6 leading-relaxed">Tidak ada presentasi yang sedang berada di tong sampah.</p>
                <a href="/presentation/u/0/"
                   class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-2.5 rounded-xl text-xs transition-colors shadow-xs">
                    <span class="material-symbols-rounded text-[18px]">folder_open</span>
                    Kembali ke Dashboard
                </a>
            </div>
        `;
    }
}
</script>
<?= $this->endSection() ?>
