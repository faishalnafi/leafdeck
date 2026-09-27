# LeafDeck — Panduan untuk AI Agents 🤖

Dokumen ini menjelaskan cara AI agents (Claude, Gemini, Copilot, dsb.) dapat berkontribusi secara efektif pada proyek **LeafDeck**.

---

## 📋 Prasyarat Sebelum Bekerja

Sebelum melakukan perubahan apapun, agent **WAJIB** membaca:

1. [`CLAUDE.md`](../CLAUDE.md) — Arsitektur, konvensi, dan aturan utama
2. [`agents/context/database-schema.md`](./context/database-schema.md) — Skema database terkini
3. [`agents/context/api-endpoints.md`](./context/api-endpoints.md) — Daftar API endpoints
4. [`agents/context/ui-components.md`](./context/ui-components.md) — Komponen UI yang tersedia

---

## 🎯 Cara Berkontribusi via Agent

### 1. Feature Request
Gunakan template di [`agents/prompts/feature-request.md`](./prompts/feature-request.md)

### 2. Bug Fix
Deskripsikan:
- File yang bermasalah (dengan path lengkap)
- Perilaku yang diharapkan vs aktual
- Error message (jika ada)

### 3. Refactoring
Selalu buat backup mental (atau commit) sebelum refactor besar.

---

## 🚫 Hal yang TIDAK Boleh Dilakukan Agent

| ❌ Dilarang | ✅ Alternatif |
|------------|--------------|
| Edit `app/Config/Routes.php` tanpa dokumentasi | Update `api-endpoints.md` bersamaan |
| Logic bisnis di Controller | Pindahkan ke `app/Services/` |
| Query database di View | Gunakan Service + API pattern |
| Hardcode API token di kode | Gunakan `.env` |
| Pakai Bootstrap atau CSS framework lain | Gunakan Tailwind CDN only |
| Pakai icon selain Google Material Symbols | Tetap pada Material Symbols |

---

## 📁 File-file Penting

| File | Fungsi |
|------|--------|
| `app/Filters/ApiAuthFilter.php` | Validasi Bearer Token untuk semua `/api/*` |
| `app/Helpers/ApiResponse.php` | Helper standar JSON response |
| `public/assets/js/api.js` | Fetch wrapper + token management di frontend |
| `app/Config/Routes.php` | Semua routing web dan API |
| `.env` | Konfigurasi environment (jangan commit!) |

---

## 🔄 Workflow yang Disarankan

```
1. Baca CLAUDE.md
2. Identifikasi layer yang perlu diubah
3. Buat/edit file sesuai layer:
   - Model → hanya query DB
   - Service → business logic
   - Controller → validasi + panggil service
   - View → tampilan + panggil API via JS
4. Update context/ jika ada perubahan schema/API
5. Test endpoint dengan format response standar
```

---

## 📡 Testing API

Gunakan format curl berikut untuk test:

```bash
# Login
curl -X POST https://yourdomain.com/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"guru@leafdeck.id","password":"password"}'

# Authenticated request
curl -X GET https://yourdomain.com/api/v1/decks \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## 🗃️ Context Files

| File | Isi | Update kapan? |
|------|-----|---------------|
| `context/database-schema.md` | ERD & skema tabel | Setiap ada migrasi baru |
| `context/api-endpoints.md` | Daftar lengkap API | Setiap ada endpoint baru |
| `context/ui-components.md` | Komponen Tailwind UI | Setiap ada komponen baru |

---

*Dokumen ini dikelola oleh tim LeafDeck. Update terakhir: September 2026.*
