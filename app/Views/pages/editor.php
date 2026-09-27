<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header Navigation -->
    <div class="flex items-center justify-between mb-6">
        <a href="/presentation/u/0/" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-emerald-600 transition-colors">
            <span class="material-symbols-rounded text-[18px]">arrow_back</span>
            <span>Kembali ke Dashboard</span>
        </a>

        <div class="flex items-center gap-2">
            <a href="/presentation/u/0/d/<?= esc($deck->nano_id) ?>/view"
               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-xl transition-colors">
                <span class="material-symbols-rounded text-[16px]">visibility</span>
                <span>Buka Presentasi</span>
            </a>
        </div>
    </div>

    <!-- Main Card Form -->
    <div class="bg-white rounded-2xl border border-slate-200 p-8 shadow-xs">
        <div class="flex items-start justify-between pb-6 border-b border-slate-100">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Pengaturan Presentasi</h1>
                <p class="text-xs text-slate-500 mt-1">Ubah judul, deskripsi materi, atau tingkat akses publik/privat.</p>
            </div>
            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full <?= $deck->is_public ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200' ?>">
                <?= $deck->is_public ? 'Publik' : 'Privat' ?>
            </span>
        </div>

        <form id="form-edit-deck" class="mt-6 space-y-5">
            <div>
                <label for="edit-title" class="block text-xs font-bold text-slate-700 mb-1.5">Judul Presentasi</label>
                <input type="text" id="edit-title" required value="<?= esc($deck->title) ?>"
                       class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            </div>

            <div>
                <label for="edit-desc" class="block text-xs font-bold text-slate-700 mb-1.5">Deskripsi Singkat</label>
                <textarea id="edit-desc" rows="3" placeholder="Tambahkan catatan materi..."
                          class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"><?= esc($deck->description ?? '') ?></textarea>
            </div>

            <!-- Visibility Radio -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">Tingkat Akses Tautan</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-emerald-500 cursor-pointer transition-colors bg-slate-50/50 has-[:checked]:bg-emerald-50/30 has-[:checked]:border-emerald-500">
                        <input type="radio" name="edit_visibility" value="1" <?= $deck->is_public ? 'checked' : '' ?> class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                <span class="material-symbols-rounded text-emerald-600 text-[16px]">public</span>
                                Publik
                            </span>
                            <p class="text-[11px] text-slate-500 mt-0.5">Siapa saja yang memiliki tautan dapat membuka presentasi tanpa login.</p>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-emerald-500 cursor-pointer transition-colors bg-slate-50/50 has-[:checked]:bg-emerald-50/30 has-[:checked]:border-emerald-500">
                        <input type="radio" name="edit_visibility" value="0" <?= !$deck->is_public ? 'checked' : '' ?> class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                <span class="material-symbols-rounded text-slate-500 text-[16px]">lock</span>
                                Privat
                            </span>
                            <p class="text-[11px] text-slate-500 mt-0.5">Hanya Anda dan admin yang dapat melihat dan membuka presentasi ini.</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Share Link Box -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Tautan Berbagi</label>
                <div class="flex items-center gap-2">
                    <input type="text" readonly value="<?= site_url("presentation/u/0/d/{$deck->nano_id}/view") ?>" id="share-link-input"
                           class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-600 font-mono select-all">
                    <button type="button" onclick="copyShareLink()"
                            class="px-4 py-2 text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-colors shrink-0 flex items-center gap-1">
                        <span class="material-symbols-rounded text-[16px]">content_copy</span>
                        <span>Salin</span>
                    </button>
                </div>
            </div>

            <!-- Submit & Delete Controls -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                <button type="button" onclick="deleteCurrentDeck()"
                        class="text-xs font-semibold text-red-600 hover:text-red-700 hover:bg-red-50 px-3 py-2 rounded-xl transition-colors flex items-center gap-1">
                    <span class="material-symbols-rounded text-[16px]">delete</span>
                    <span>Hapus Presentasi</span>
                </button>

                <div class="flex items-center gap-2">
                    <a href="/presentation/u/0/" class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-xl">Batal</a>
                    <button type="submit" id="btn-save-edit"
                            class="px-5 py-2.5 text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl shadow-xs transition-colors flex items-center gap-2">
                        <span class="material-symbols-rounded text-[16px]">save</span>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const nanoId = <?= json_encode($deck->nano_id) ?>;
    const form = document.getElementById('form-edit-deck');
    const saveBtn = document.getElementById('btn-save-edit');

    function copyShareLink() {
        const input = document.getElementById('share-link-input');
        if (navigator.clipboard) {
            navigator.clipboard.writeText(input.value).then(() => {
                showToast('Tautan berhasil disalin ke clipboard!', 'success');
            });
        } else {
            input.select();
            document.execCommand('copy');
            showToast('Tautan disalin ke clipboard!', 'success');
        }
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const title = document.getElementById('edit-title').value.trim();
        const description = document.getElementById('edit-desc').value.trim();
        const isPublicRadio = document.querySelector('input[name="edit_visibility"]:checked');
        const isPublic = isPublicRadio ? parseInt(isPublicRadio.value) : 0;

        if (!title) {
            showToast('Judul presentasi wajib diisi', 'error');
            return;
        }

        setLoading(saveBtn, true, 'Menyimpan...');

        try {
            const res = await API.decks.update(nanoId, {
                title: title,
                description: description,
                is_public: isPublic
            });

            if (res && res.status) {
                showToast('Perubahan berhasil disimpan!', 'success');
                setTimeout(() => {
                    window.location.href = '/presentation/u/0/';
                }, 800);
            } else {
                showToast(res?.message || 'Gagal menyimpan perubahan', 'error');
            }
        } catch (err) {
            console.error('Update error:', err);
            showToast('Gagal menyimpan perubahan', 'error');
        } finally {
            setLoading(saveBtn, false, 'Simpan Perubahan');
        }
    });

    async function deleteCurrentDeck() {
        if (!confirm('Apakah Anda yakin ingin memindahkan presentasi ini ke sampah (soft delete)?')) {
            return;
        }

        try {
            const res = await API.decks.delete(nanoId);
            if (res && res.status) {
                showToast('Presentasi berhasil dihapus', 'success');
                setTimeout(() => {
                    window.location.href = '/presentation/u/0/';
                }, 600);
            } else {
                showToast(res?.message || 'Gagal menghapus presentasi', 'error');
            }
        } catch (err) {
            showToast('Gagal menghapus presentasi', 'error');
        }
    }
</script>
<?= $this->endSection() ?>
