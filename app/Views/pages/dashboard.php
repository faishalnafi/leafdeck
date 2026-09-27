<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <!-- Top Action / New Presentation Section (Google Slides style) -->
    <div class="mb-10 bg-white/70 border border-slate-200/80 rounded-2xl p-6 backdrop-blur-sm shadow-xs">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Mulai presentasi baru</h2>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4">
            <!-- Blank / Upload HTML Card -->
            <div class="group cursor-pointer" onclick="openUploadModal()">
                <div class="aspect-[4/3] bg-white border-2 border-dashed border-slate-300 group-hover:border-emerald-500 rounded-xl flex flex-col items-center justify-center transition-all duration-200 group-hover:shadow-md group-hover:bg-emerald-50/20">
                    <span class="material-symbols-rounded text-[40px] text-emerald-600 group-hover:scale-110 transition-transform">add_circle</span>
                </div>
                <p class="text-xs font-semibold text-slate-700 mt-2 text-center truncate group-hover:text-emerald-600">Unggah HTML Baru</p>
            </div>
        </div>
    </div>

    <!-- Recent Presentations Section -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <span class="material-symbols-rounded text-emerald-600">folder_open</span>
                Presentasi Terkini
            </h2>
            <div class="flex items-center gap-3">
                <a href="/presentation/u/0/trash"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-red-50 hover:text-red-600 hover:border-red-200 text-xs font-semibold text-slate-600 transition-all shadow-2xs group"
                   title="Buka Tong Sampah">
                    <span class="material-symbols-rounded text-[17px] text-slate-400 group-hover:text-red-500">delete</span>
                    Tong Sampah
                </a>
                <span class="text-xs text-slate-500 font-medium">Milik saya</span>
            </div>
        </div>

        <?php if (empty($decks)): ?>
            <!-- Empty State -->
            <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center max-w-md mx-auto my-8 shadow-xs">
                <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-rounded text-[32px]">menu_book</span>
                </div>
                <h3 class="text-base font-bold text-slate-800">Belum ada materi presentasi</h3>
                <p class="text-xs text-slate-500 mt-1 mb-6 leading-relaxed">Mulai dengan mengunggah file HTML murni untuk ditampilkan sebagai e-book interaktif.</p>
                <button onclick="openUploadModal()"
                        class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-2.5 rounded-xl text-xs transition-colors shadow-xs">
                    <span class="material-symbols-rounded text-[18px]">upload</span>
                    Unggah Sekarang
                </button>
            </div>
        <?php else: ?>
            <!-- Deck Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5" id="deck-grid">
                <?php foreach ($decks as $deck): ?>
                    <div id="deck-card-<?= esc($deck->nano_id) ?>"
                         class="bg-white rounded-xl border border-slate-200 hover:shadow-md transition-all duration-200 group flex flex-col relative">

                        <!-- Preview Thumbnail / Link -->
                        <a href="/presentation/u/0/d/<?= esc($deck->nano_id) ?>/view" class="block aspect-[16/10] bg-slate-100 relative rounded-t-xl overflow-hidden">
                            <?php if (!empty($deck->thumbnail)): ?>
                                <img src="<?= esc($deck->thumbnail) ?>" alt="<?= esc($deck->title) ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full flex flex-col items-center justify-center text-slate-400 group-hover:text-emerald-600 transition-colors">
                                    <span class="material-symbols-rounded text-[48px]">slideshow</span>
                                </div>
                            <?php endif; ?>

                            <!-- View count badge -->
                            <span class="absolute top-2 left-2 bg-black/60 backdrop-blur-xs text-white text-[10px] font-medium px-2 py-0.5 rounded-full flex items-center gap-1">
                                <span class="material-symbols-rounded text-[12px]">visibility</span>
                                <?= esc($deck->view_count) ?>
                            </span>
                        </a>

                        <!-- Card Body -->
                        <div class="p-4 flex-1 flex flex-col justify-between">
                            <div>
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="text-sm font-semibold text-slate-900 truncate flex-1" title="<?= esc($deck->title) ?>">
                                        <a href="/presentation/u/0/d/<?= esc($deck->nano_id) ?>/view" class="hover:text-emerald-600">
                                            <?= esc($deck->title) ?>
                                        </a>
                                    </h3>

                                    <!-- Quick Actions Menu Dropdown -->
                                    <div class="relative shrink-0">
                                        <button onclick="toggleCardMenu('menu-<?= esc($deck->nano_id) ?>', event)"
                                                class="text-slate-400 hover:text-slate-700 p-1 rounded-lg hover:bg-slate-100 transition-colors"
                                                title="Opsi Lainnya">
                                            <span class="material-symbols-rounded text-[18px]">more_vert</span>
                                        </button>

                                        <!-- Dropdown Menu -->
                                        <div id="menu-<?= esc($deck->nano_id) ?>"
                                             class="hidden absolute right-0 mt-1 w-48 bg-white rounded-xl shadow-xl border border-slate-100 py-1.5 z-30 text-xs font-medium text-slate-700 animate-in fade-in zoom-in duration-100">
                                            <button onclick="copyDeckLink('<?= site_url("presentation/u/0/d/{$deck->nano_id}/view") ?>')"
                                                    class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-2.5">
                                                <span class="material-symbols-rounded text-[16px] text-slate-400">link</span>
                                                <span>Salin Tautan</span>
                                            </button>
                                            <a href="/presentation/u/0/d/<?= esc($deck->nano_id) ?>/edit"
                                               class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-2.5 text-slate-700">
                                                <span class="material-symbols-rounded text-[16px] text-slate-400">edit</span>
                                                <span>Pengaturan / Edit</span>
                                            </a>
                                            <button onclick="toggleVisibility('<?= esc($deck->nano_id) ?>', <?= (int)$deck->is_public ?>)"
                                                    class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-2.5">
                                                <span class="material-symbols-rounded text-[16px] text-slate-400"><?= $deck->is_public ? 'lock' : 'public' ?></span>
                                                <span>Ubah ke <?= $deck->is_public ? 'Privat' : 'Publik' ?></span>
                                            </button>
                                            <div class="border-t border-slate-100 my-1"></div>
                                            <button onclick="openDeleteModal('<?= esc($deck->nano_id) ?>', '<?= esc($deck->title, 'js') ?>')"
                                                    class="w-full text-left px-3.5 py-2 hover:bg-red-50 text-red-600 flex items-center gap-2.5">
                                                <span class="material-symbols-rounded text-[16px]">delete</span>
                                                <span>Hapus (Soft Delete)</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <p class="text-[11px] text-slate-400 mt-1">
                                    <?= date('d M Y, H:i', strtotime($deck->created_at)) ?>
                                </p>
                            </div>

                            <!-- Card Footer: Link, Visibility Toggle & Direct Trash Icon -->
                            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                                <a href="/presentation/u/0/d/<?= esc($deck->nano_id) ?>/view"
                                   class="text-emerald-600 hover:text-emerald-700 font-semibold flex items-center gap-1">
                                    <span>Buka Presentasi</span>
                                    <span class="material-symbols-rounded text-[14px]">arrow_forward</span>
                                </a>

                                <div class="flex items-center gap-1.5">
                                    <!-- Clickable Visibility Badge -->
                                    <button type="button"
                                            id="badge-<?= esc($deck->nano_id) ?>"
                                            onclick="toggleVisibility('<?= esc($deck->nano_id) ?>', <?= (int)$deck->is_public ?>)"
                                            title="Klik untuk mengubah status akses Publik/Privat"
                                            class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded-full font-semibold transition-all duration-200 cursor-pointer <?= $deck->is_public ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-600 border border-slate-200 hover:bg-slate-200' ?>">
                                        <span class="material-symbols-rounded text-[13px]"><?= $deck->is_public ? 'public' : 'lock' ?></span>
                                        <span class="badge-text"><?= $deck->is_public ? 'Publik' : 'Privat' ?></span>
                                    </button>

                                    <!-- Direct Delete Button (Ikon Tempat Sampah) -->
                                    <button type="button"
                                            onclick="openDeleteModal('<?= esc($deck->nano_id) ?>', '<?= esc($deck->title, 'js') ?>')"
                                            class="text-slate-400 hover:text-red-600 hover:bg-red-50 p-1 rounded-lg transition-colors flex items-center justify-center"
                                            title="Hapus Presentasi (Soft Delete)">
                                        <span class="material-symbols-rounded text-[18px]">delete</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Upload Modal with Drag and Drop Zone -->
<div id="upload-modal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-200 animate-in fade-in zoom-in duration-150">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                <span class="material-symbols-rounded text-emerald-600">upload_file</span>
                Unggah File HTML Materi
            </h3>
            <button onclick="closeUploadModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                <span class="material-symbols-rounded text-[20px]">close</span>
            </button>
        </div>

        <form id="form-upload-deck" class="mt-4 space-y-4">
            <!-- Drag and Drop Dropzone -->
            <div id="dropzone"
                 class="border-2 border-dashed border-slate-300 hover:border-emerald-500 rounded-2xl p-6 text-center cursor-pointer transition-all duration-200 bg-slate-50/50 hover:bg-emerald-50/30">
                <input type="file" id="deck-file-input" accept=".html,.htm" class="hidden">

                <!-- Default Unselected State -->
                <div id="dropzone-empty" class="flex flex-col items-center">
                    <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mb-3">
                        <span class="material-symbols-rounded text-[32px]">cloud_upload</span>
                    </div>
                    <p class="text-sm font-bold text-slate-700">Tarik &amp; lepas file HTML ke sini</p>
                    <p class="text-xs text-slate-500 mt-1">atau <span class="text-emerald-600 font-semibold underline underline-offset-2">telusuri dari komputer</span></p>
                    <span class="inline-block mt-3 text-[11px] text-slate-400 bg-white border border-slate-200 px-2.5 py-1 rounded-full">
                        Mendukung .html dan .htm murni (maks. 10MB)
                    </span>
                </div>

                <!-- Selected File Preview State -->
                <div id="dropzone-selected" class="hidden flex items-center justify-between bg-white border border-emerald-200 p-3.5 rounded-xl shadow-xs">
                    <div class="flex items-center gap-3 text-left truncate">
                        <div class="w-10 h-10 bg-emerald-100 text-emerald-700 rounded-lg flex items-center justify-center shrink-0">
                            <span class="material-symbols-rounded text-[22px]">html</span>
                        </div>
                        <div class="truncate">
                            <p id="selected-filename" class="text-xs font-bold text-slate-800 truncate"></p>
                            <p id="selected-filesize" class="text-[11px] text-slate-400"></p>
                        </div>
                    </div>
                    <button type="button" id="btn-remove-file" class="text-slate-400 hover:text-red-500 p-1.5 rounded-lg shrink-0" title="Ganti File">
                        <span class="material-symbols-rounded text-[18px]">delete</span>
                    </button>
                </div>
            </div>

            <!-- Title Field -->
            <div>
                <label for="deck-title" class="block text-xs font-semibold text-slate-700 mb-1">Judul Presentasi</label>
                <input type="text" id="deck-title" required placeholder="Contoh: Modul Fisika Bab 1"
                       class="w-full px-3.5 py-2.5 text-xs border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            </div>

            <!-- Description Field -->
            <div>
                <label for="deck-desc" class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi Singkat (Opsional)</label>
                <textarea id="deck-desc" rows="2" placeholder="Catatan materi atau ringkasan kompetensi dasar..."
                          class="w-full px-3.5 py-2 text-xs border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"></textarea>
            </div>

            <!-- Visibility Toggle -->
            <div class="flex items-center gap-2">
                <input type="checkbox" id="deck-public" class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer">
                <label for="deck-public" class="text-xs font-medium text-slate-700 cursor-pointer">
                    Jadikan presentasi publik (dapat diakses bersama siswa)
                </label>
            </div>

            <!-- Action Buttons -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeUploadModal()"
                        class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Batal</button>
                <button type="submit" id="btn-submit-upload"
                        class="px-5 py-2.5 text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl shadow-xs transition-colors flex items-center gap-2">
                    <span class="material-symbols-rounded text-[16px]">upload</span>
                    <span>Mulai Unggah</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Soft Delete Confirmation Modal -->
<div id="delete-modal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-xl border border-slate-200 animate-in fade-in zoom-in duration-150 text-center">
        <div class="w-12 h-12 bg-red-50 text-red-600 rounded-2xl flex items-center justify-center mx-auto mb-3 border border-red-100">
            <span class="material-symbols-rounded text-[26px]">delete</span>
        </div>

        <h3 class="font-bold text-slate-800 text-sm">Pindahkan ke Tong Sampah?</h3>
        <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
            Presentasi <strong id="delete-deck-title" class="text-slate-700"></strong> akan dipindahkan ke Tong Sampah (soft delete) dan dapat dipulihkan kapan saja.
        </p>

        <div class="mt-5 flex items-center justify-center gap-2">
            <button type="button" onclick="closeDeleteModal()"
                    class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                Batal
            </button>
            <button type="button" id="btn-confirm-delete" onclick="executeDelete()"
                    class="px-4 py-2 text-xs font-semibold bg-red-600 hover:bg-red-700 text-white rounded-xl shadow-xs transition-colors">
                Hapus Sekarang
            </button>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let selectedFile = null;
    let deckToDelete = null;

    const modal = document.getElementById('upload-modal');
    const deleteModal = document.getElementById('delete-modal');
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('deck-file-input');
    const emptyState = document.getElementById('dropzone-empty');
    const selectedState = document.getElementById('dropzone-selected');
    const filenameEl = document.getElementById('selected-filename');
    const filesizeEl = document.getElementById('selected-filesize');
    const removeBtn = document.getElementById('btn-remove-file');
    const titleInput = document.getElementById('deck-title');
    const form = document.getElementById('form-upload-deck');
    const submitBtn = document.getElementById('btn-submit-upload');

    // Close open menus when clicking anywhere outside
    document.addEventListener('click', () => {
        document.querySelectorAll('[id^="menu-"]').forEach(el => el.classList.add('hidden'));
    });

    function toggleCardMenu(menuId, event) {
        event.stopPropagation();
        const targetMenu = document.getElementById(menuId);
        document.querySelectorAll('[id^="menu-"]').forEach(el => {
            if (el !== targetMenu) el.classList.add('hidden');
        });
        targetMenu.classList.toggle('hidden');
    }

    function openUploadModal() {
        modal.classList.remove('hidden');
    }

    function closeUploadModal() {
        modal.classList.add('hidden');
        resetFileSelection();
    }

    function resetFileSelection() {
        selectedFile = null;
        fileInput.value = '';
        emptyState.classList.remove('hidden');
        selectedState.classList.add('hidden');
    }

    function handleFile(file) {
        if (!file) return;

        const ext = file.name.split('.').pop().toLowerCase();
        if (ext !== 'html' && ext !== 'htm') {
            showToast('Hanya file .html atau .htm yang didukung', 'error');
            return;
        }

        selectedFile = file;
        filenameEl.textContent = file.name;
        filesizeEl.textContent = (file.size / 1024).toFixed(1) + ' KB';

        emptyState.classList.add('hidden');
        selectedState.classList.remove('hidden');

        // Auto-fill title jika kosong
        if (!titleInput.value.trim()) {
            const rawName = file.name.substring(0, file.name.lastIndexOf('.')) || file.name;
            titleInput.value = rawName.replace(/[_-]/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        }
    }

    dropzone.addEventListener('click', (e) => {
        if (e.target.closest('#btn-remove-file')) return;
        fileInput.click();
    });

    fileInput.addEventListener('change', () => {
        if (fileInput.files && fileInput.files[0]) {
            handleFile(fileInput.files[0]);
        }
    });

    removeBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        resetFileSelection();
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('border-emerald-500', 'bg-emerald-50/50');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('border-emerald-500', 'bg-emerald-50/50');
        }, false);
    });

    dropzone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files && files[0]) {
            handleFile(files[0]);
        }
    });

    // Form Upload Submit Handler
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!selectedFile) {
            showToast('Silakan pilih atau tarik file HTML terlebih dahulu', 'error');
            return;
        }

        const title = titleInput.value.trim();
        if (!title) {
            showToast('Judul presentasi wajib diisi', 'error');
            return;
        }

        setLoading(submitBtn, true, 'Mengunggah...');

        const formData = new FormData();
        formData.append('title', title);
        formData.append('description', document.getElementById('deck-desc').value.trim());
        formData.append('is_public', document.getElementById('deck-public').checked ? 1 : 0);
        formData.append('file', selectedFile);

        try {
            const res = await API.decks.upload(formData);

            if (res && res.status) {
                showToast('Presentasi berhasil diunggah!', 'success');
                closeUploadModal();
                setTimeout(() => {
                    if (res.data && res.data.url) {
                        window.location.href = res.data.url;
                    } else {
                        window.location.reload();
                    }
                }, 700);
            } else {
                showToast(res?.message || 'Gagal mengunggah presentasi', 'error');
                setLoading(submitBtn, false, 'Mulai Unggah');
            }
        } catch (err) {
            console.error('Upload Error:', err);
            showToast('Terjadi kesalahan koneksi saat mengunggah', 'error');
            setLoading(submitBtn, false, 'Mulai Unggah');
        }
    });

    // Salin Tautan ke Clipboard
    function copyDeckLink(url) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(url).then(() => {
                showToast('Tautan berhasil disalin ke clipboard!', 'success');
            }).catch(() => {
                fallbackCopy(url);
            });
        } else {
            fallbackCopy(url);
        }
    }

    function fallbackCopy(text) {
        const input = document.createElement('input');
        input.value = text;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        showToast('Tautan disalin ke clipboard!', 'success');
    }

    // Toggle Public / Private Visibility
    async function toggleVisibility(nanoId, currentStatus) {
        const newStatus = currentStatus === 1 ? 0 : 1;
        const badge = document.getElementById(`badge-${nanoId}`);

        try {
            const res = await API.decks.update(nanoId, { is_public: newStatus });

            if (res && res.status) {
                const isPub = newStatus === 1;
                showToast(`Presentasi diubah menjadi ${isPub ? 'Publik' : 'Privat'}`, 'success');

                // Update UI badge
                if (badge) {
                    badge.setAttribute('onclick', `toggleVisibility('${nanoId}', ${newStatus})`);
                    badge.className = `inline-flex items-center gap-1 text-[11px] px-2.5 py-1 rounded-full font-semibold transition-all duration-200 cursor-pointer ${
                        isPub
                            ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100'
                            : 'bg-slate-100 text-slate-600 border border-slate-200 hover:bg-slate-200'
                    }`;
                    badge.innerHTML = `
                        <span class="material-symbols-rounded text-[13px]">${isPub ? 'public' : 'lock'}</span>
                        <span class="badge-text">${isPub ? 'Publik' : 'Privat'}</span>
                    `;
                }
            } else {
                showToast(res?.message || 'Gagal mengubah status visibilitas', 'error');
            }
        } catch (err) {
            console.error('Update visibility error:', err);
            showToast('Gagal mengubah visibilitas', 'error');
        }
    }

    // Soft Delete Handlers
    function openDeleteModal(nanoId, title) {
        deckToDelete = nanoId;
        document.getElementById('delete-deck-title').textContent = `"${title}"`;
        deleteModal.classList.remove('hidden');
    }

    function closeDeleteModal() {
        deleteModal.classList.add('hidden');
        deckToDelete = null;
    }

    async function executeDelete() {
        if (!deckToDelete) return;

        const btn = document.getElementById('btn-confirm-delete');
        setLoading(btn, true, 'Menghapus...');

        try {
            const res = await API.decks.delete(deckToDelete);

            if (res && res.status) {
                showToast('Presentasi dipindahkan ke Tong Sampah. <a href="/presentation/u/0/trash" class="underline font-bold text-emerald-800 ml-1">Lihat Tong Sampah</a>', 'success', 5000);
                const card = document.getElementById(`deck-card-${deckToDelete}`);
                if (card) {
                    card.classList.add('opacity-0', 'scale-95');
                    setTimeout(() => card.remove(), 250);
                }
                closeDeleteModal();
            } else {
                showToast(res?.message || 'Gagal menghapus presentasi', 'error');
            }
        } catch (err) {
            console.error('Delete error:', err);
            showToast('Gagal menghapus presentasi', 'error');
        } finally {
            setLoading(btn, false, 'Hapus Sekarang');
        }
    }
</script>
<?= $this->endSection() ?>
