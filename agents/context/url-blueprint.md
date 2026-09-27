# URL Blueprint & Roadmap — LeafDeck

> Dokumen ini mencatat keputusan arsitektur URL saat ini dan rencana masa depan.
> **Update file ini setiap ada perubahan pola URL.**

---

## 📍 Status Saat Ini: Single Account (`u/0`)

LeafDeck mengikuti konvensi URL **Google Docs/Slides** dengan pola `u/0`.

### Alasan `u/0`
- SSO sekolah saat ini hanya mendukung **single login account** per sesi
- Nilai `0` diabaikan di backend (hardcoded single session)
- Konsisten dengan ekosistem aplikasi sekolah lainnya

### Pola URL Aktif

```
# Dashboard / Home
leafdeck.sman3mjk.sch.id/presentation/u/0/

# Viewer e-book (tampil presentasi)
leafdeck.sman3mjk.sch.id/presentation/u/0/d/{nanoid}/view

# Editor metadata
leafdeck.sman3mjk.sch.id/presentation/u/0/d/{nanoid}/edit

# SSO Callback
leafdeck.sman3mjk.sch.id/sso/callback?token={jwt}

# SSO Logout
leafdeck.sman3mjk.sch.id/sso/logout
```

---

## 🔮 Blueprint: Multi-Account (`u/0`, `u/1`, `u/2`, ...)

> ⚠️ **BELUM AKTIF** — Menunggu SSO sekolah mendukung multi-account login.

### Kapan Diaktifkan
Aktifkan fitur ini ketika:
- [ ] SSO sekolah mendukung login beberapa akun sekaligus dalam satu browser
- [ ] SSO mengembalikan `account_index` (0, 1, 2...) di JWT payload
- [ ] Ada kebutuhan guru punya akun pribadi + akun dinas sekaligus

### Yang Perlu Diubah Saat Aktif

**1. Routes.php** — nilai `(:num)` sudah siap, tinggal aktifkan logika:
```php
// Saat ini: nilai u/0 diabaikan
// Nanti: gunakan nilai u/{n} untuk pilih session index
$routes->group('presentation/u/(:num)', function ($routes) {
    // $1 = account index, teruskan ke controller
});
```

**2. `SsoController::callback`** — simpan token per account index:
```php
// Saat ini: simpan 1 token di session
// Nanti: simpan array token session['accounts'][0], [1], [2]...
```

**3. `ApiAuthFilter`** — pilih token berdasarkan account index dari URL

**4. Frontend `api.js`** — inject account index ke setiap request

---

## 🗺️ Phase Roadmap URL

```
Phase 1 (AKTIF)
└── /presentation/u/0/          ← Slides / E-Book

Phase 2 (BELUM — tunggu konten editor)
└── /document/u/0/              ← Dokumen (Google Docs-style)

Phase 3 (BELUM — tunggu spreadsheet viewer)
└── /spreadsheet/u/0/           ← Spreadsheet (Google Sheets-style)

Phase Bonus
└── /drive/u/0/                 ← File Manager (Google Drive-style)
└── /form/u/0/                  ← Formulir (Google Forms-style)
```

---

## 📌 Catatan untuk Agent

- Jangan ubah pola `u/0` tanpa konfirmasi eksplisit dari developer
- Semua link internal harus menggunakan helper atau base path `presentation/u/0`
- NanoID digunakan sebagai `{nanoid}` — 21 karakter, URL-safe
- Saat SSO multi-account aktif, buat branch/PR terpisah — jangan langsung ke main
