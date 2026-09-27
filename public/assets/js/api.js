/**
 * api.js — LeafDeck API Client
 *
 * Fetch wrapper dengan Bearer Token management.
 * Semua request frontend ke API menggunakan fungsi dari file ini.
 */

const API_BASE = '/api/v1';

// ─── Token Management ────────────────────────────────────────────────────────

const Auth = {
    getToken: () => localStorage.getItem('leafdeck_token'),
    setToken: (token) => localStorage.setItem('leafdeck_token', token),
    removeToken: () => localStorage.removeItem('leafdeck_token'),
    getUser: () => {
        const u = localStorage.getItem('leafdeck_user');
        return u ? JSON.parse(u) : null;
    },
    setUser: (user) => localStorage.setItem('leafdeck_user', JSON.stringify(user)),
    removeUser: () => localStorage.removeItem('leafdeck_user'),
    isLoggedIn: () => !!localStorage.getItem('leafdeck_token'),
    clear: () => {
        Auth.removeToken();
        Auth.removeUser();
    },
};

// ─── Core Fetch Wrapper ──────────────────────────────────────────────────────

/**
 * Kirim request ke API dengan Bearer Token otomatis.
 *
 * @param {string} endpoint  - Contoh: '/auth/login'
 * @param {object} options   - Fetch options (method, body, dsb.)
 * @returns {Promise<object>} - Parsed JSON response
 */
async function apiRequest(endpoint, options = {}) {
    const token = Auth.getToken();

    const headers = {
        'Content-Type': 'application/json',
        ...options.headers,
    };

    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    // Jika body adalah FormData, hapus Content-Type agar browser set otomatis
    if (options.body instanceof FormData) {
        delete headers['Content-Type'];
    }

    const config = {
        method: options.method || 'GET',
        headers,
        ...options,
    };

    try {
        const response = await fetch(`${API_BASE}${endpoint}`, config);
        const json = await response.json();

        // Token kedaluwarsa → redirect ke login
        if (response.status === 401) {
            Auth.clear();
            window.location.href = '/';
            return;
        }

        return json;
    } catch (error) {
        console.error('[LeafDeck API Error]', error);
        return {
            status: false,
            code: 500,
            message: 'Koneksi ke server gagal',
            data: null,
        };
    }
}

// ─── Shorthand Methods ───────────────────────────────────────────────────────

const API = {
    get: (endpoint) => apiRequest(endpoint, { method: 'GET' }),

    post: (endpoint, body) => apiRequest(endpoint, {
        method: 'POST',
        body: JSON.stringify(body),
    }),

    postForm: (endpoint, formData) => apiRequest(endpoint, {
        method: 'POST',
        body: formData,
    }),

    put: (endpoint, body) => apiRequest(endpoint, {
        method: 'PUT',
        body: JSON.stringify(body),
    }),

    delete: (endpoint) => apiRequest(endpoint, { method: 'DELETE' }),

    // ─── Auth ──────────────────────────────────────────────────────────────
    auth: {
        login: async (email, password) => {
            const res = await API.post('/auth/login', { email, password });
            if (res?.status) {
                Auth.setToken(res.data.token);
                Auth.setUser(res.data.user);
            }
            return res;
        },
        logout: async () => {
            const res = await API.post('/auth/logout');
            Auth.clear();
            return res;
        },
    },

    // ─── Deck ──────────────────────────────────────────────────────────────
    decks: {
        list: () => API.get('/decks'),
        trash: () => API.get('/decks/trash'),
        get: (id) => API.get(`/decks/${id}`),
        upload: (formData) => API.postForm('/decks', formData),
        update: (id, body) => API.put(`/decks/${id}`, body),
        delete: (id) => API.delete(`/decks/${id}`),
        restore: (id) => API.post(`/decks/${id}/restore`),
        forceDelete: (id) => API.delete(`/decks/${id}/force`),
    },

    // ─── User ──────────────────────────────────────────────────────────────
    user: {
        profile: () => API.get('/users/profile'),
        updateProfile: (body) => API.put('/users/profile', body),
        changePassword: (body) => API.put('/users/password', body),
    },
};

// Export global
window.API  = API;
window.Auth = Auth;
