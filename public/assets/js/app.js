/**
 * app.js — LeafDeck Main JS
 *
 * Entry point utama untuk semua halaman.
 * Load api.js terlebih dahulu sebelum file ini.
 */

// ─── DOM Ready ───────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    initToast();
    initLogoutBtn();
    protectAuthPages();
});

// ─── Toast Notification ──────────────────────────────────────────────────────

let toastTimeout;

/**
 * Tampilkan toast notifikasi.
 * @param {string} message
 * @param {'success'|'error'|'info'} type
 * @param {number} duration - ms
 */
function showToast(message, type = 'success', duration = 3500) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const icons = {
        success: 'check_circle',
        error: 'error',
        info: 'info',
    };

    const colors = {
        success: 'bg-emerald-50 border-emerald-200 text-emerald-700',
        error: 'bg-red-50 border-red-200 text-red-700',
        info: 'bg-blue-50 border-blue-200 text-blue-700',
    };

    const toast = document.createElement('div');
    toast.className = `flex items-center gap-3 border px-4 py-3 rounded-xl shadow-lg transition-all duration-300 translate-y-0 opacity-100 ${colors[type]}`;
    toast.innerHTML = `
        <span class="material-symbols-rounded text-[20px]">${icons[type]}</span>
        <p class="text-sm font-medium">${message}</p>
    `;

    container.appendChild(toast);

    clearTimeout(toastTimeout);
    toastTimeout = setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-2');
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

window.showToast = showToast;
window.Toast = { show: showToast };

// ─── Init Toast Container ────────────────────────────────────────────────────

function initToast() {
    if (!document.getElementById('toast-container')) {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'fixed bottom-6 right-6 z-50 flex flex-col gap-2 max-w-sm';
        document.body.appendChild(container);
    }
}

// ─── Logout Helper ───────────────────────────────────────────────────────────

async function handleLogout() {
    try {
        if (typeof Auth !== 'undefined' && Auth.clear) {
            Auth.clear();
        }
        if (typeof API !== 'undefined' && API.auth && API.auth.logout) {
            await API.auth.logout();
        }
    } catch (e) {
        console.error('Logout error:', e);
    }
    window.location.href = '/logout';
}

window.handleLogout = handleLogout;

function initLogoutBtn() {
    document.querySelectorAll('#btn-logout, #btn-logout-mobile, .btn-do-logout').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            btn.setAttribute('disabled', 'true');
            btn.innerHTML = '<span class="material-symbols-rounded animate-spin text-[16px]">progress_activity</span> Keluar...';
            await handleLogout();
        });
    });
}

// ─── Protect Pages ───────────────────────────────────────────────────────────

function protectAuthPages() {
    const isProtected = document.body.dataset.protected === 'true';
    if (isProtected && !Auth.isLoggedIn()) {
        window.location.href = '/';
    }
}

// ─── Loading State Helper ────────────────────────────────────────────────────

function setLoading(btn, isLoading, defaultText = null) {
    if (isLoading) {
        btn.disabled = true;
        btn.dataset.originalText = btn.innerHTML;
        btn.innerHTML = `
            <svg class="animate-spin h-4 w-4 mr-2 inline" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
            Memproses...
        `;
    } else {
        btn.disabled = false;
        btn.innerHTML = btn.dataset.originalText || defaultText || 'Submit';
    }
}

window.setLoading = setLoading;
