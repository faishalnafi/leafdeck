/**
 * viewer.js — LeafDeck Presentation E-Book Viewer Controller
 */

document.addEventListener('DOMContentLoaded', () => {
    const iframe = document.getElementById('deck-viewer-frame');
    const fullscreenBtn = document.getElementById('btn-fullscreen-toggle');
    const loadingOverlay = document.getElementById('viewer-loading');

    // Sembunyikan loading state saat iframe selesai memuat
    if (iframe) {
        iframe.addEventListener('load', () => {
            if (loadingOverlay) {
                loadingOverlay.classList.add('opacity-0', 'pointer-events-none');
                setTimeout(() => loadingOverlay.remove(), 300);
            }
        });
    }

    // Fullscreen Toggle
    if (fullscreenBtn) {
        fullscreenBtn.addEventListener('click', toggleFullscreen);
    }

    function toggleFullscreen() {
        if (!document.fullscreenElement) {
            const target = iframe || document.documentElement;
            if (target.requestFullscreen) {
                target.requestFullscreen().catch(err => {
                    console.warn('Fullscreen request failed:', err);
                });
            }
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            }
        }
    }

    // Dengarkan perubahan state fullscreen
    document.addEventListener('fullscreenchange', () => {
        if (fullscreenBtn) {
            const icon = fullscreenBtn.querySelector('.material-symbols-rounded');
            if (icon) {
                icon.textContent = document.fullscreenElement ? 'fullscreen_exit' : 'fullscreen';
            }
        }
    });

    // Keyboard Shortcuts
    document.addEventListener('keydown', (e) => {
        // Jangan tangkap shortcut jika user sedang mengetik input
        if (['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) return;

        if (e.key === 'f' || e.key === 'F') {
            toggleFullscreen();
        }
    });
});
