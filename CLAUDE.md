# CLAUDE.md — LeafDeck Agent Context

> Dokumen ini adalah panduan konteks untuk AI agents (Claude, Gemini, GPT, dsb.) yang bekerja pada proyek **LeafDeck**.

---

## 🍃 Tentang Proyek

**LeafDeck** adalah webapp presentasi berbasis web yang memungkinkan guru mengunggah file HTML murni dan menampilkannya seperti e-book interaktif di hadapan siswa. Nama resmi akronim:

- **LEAF** = Layanan Edukasi Antarmuka Fleksibel
- **DECK** = Distribusi E-book Cerdas untuk Kelas

---

## 🏗️ Arsitektur

LeafDeck menggunakan arsitektur **Monolith MVC berlapis (Layered MVC Monolith)** dengan komunikasi antar layer menggunakan **API Token**.

```
┌─────────────────────────────────────┐
│         PRESENTATION LAYER          │
│   Views (CI4 Views + Tailwind CDN)  │
│   Frontend: Vanilla JS + Fetch API  │
│   Icons: Google Material Symbols    │
│   Fonts: Google Fonts               │
└────────────────┬────────────────────┘
                 │ HTTP Request + Bearer Token
┌────────────────▼────────────────────┐
│           API LAYER                 │
│   app/Controllers/Api/              │
│   Token validation via Filter       │
│   JSON Response only                │
└────────────────┬────────────────────┘
                 │ Method call
┌────────────────▼────────────────────┐
│          SERVICE LAYER              │
│   app/Services/                     │
│   Business logic & validation       │
│   Orchestrates Models               │
└────────────────┬────────────────────┘
                 │ Query
┌────────────────▼────────────────────┐
│           MODEL LAYER               │
│   app/Models/                       │
│   CodeIgniter4 Model (ORM)          │
│   MySQL / MariaDB                   │
└─────────────────────────────────────┘
```

---

## 📁 Struktur Folder

```
leafdeck/
├── app/
│   ├── Config/
│   │   ├── App.php
│   │   ├── Database.php
│   │   ├── Filters.php          ← Register ApiAuthFilter
│   │   └── Routes.php           ← Web + API routes
│   ├── Controllers/
│   │   ├── Api/                 ← API endpoints (JSON only)
│   │   │   ├── AuthController.php
│   │   │   ├── DeckController.php
│   │   │   └── UserController.php
│   │   └── Web/                 ← Web page controllers
│   │       ├── HomeController.php
│   │       ├── DashboardController.php
│   │       └── ViewerController.php
│   ├── Filters/
│   │   └── ApiAuthFilter.php    ← Bearer token validator
│   ├── Helpers/
│   │   └── ApiResponse.php      ← Standar JSON response helper
│   ├── Models/
│   │   ├── UserModel.php
│   │   ├── DeckModel.php
│   │   └── TokenModel.php
│   ├── Services/
│   │   ├── AuthService.php
│   │   ├── DeckService.php
│   │   └── FileService.php
│   └── Views/
│       ├── layouts/
│       │   ├── main.php         ← Layout utama (Tailwind + Google Fonts)
│       │   └── auth.php         ← Layout halaman auth
│       ├── pages/
│       │   ├── home.php
│       │   ├── dashboard.php
│       │   └── viewer.php       ← Tampilan e-book/presentasi
│       └── components/
│           ├── navbar.php
│           └── sidebar.php
├── public/
│   ├── assets/
│   │   ├── js/
│   │   │   ├── app.js           ← Main JS entry point
│   │   │   ├── api.js           ← API client (fetch wrapper + token)
│   │   │   └── viewer.js        ← E-book viewer logic
│   │   └── css/
│   │       └── custom.css       ← Custom CSS tambahan
│   └── uploads/
│       └── decks/               ← Folder upload HTML files
├── agents/
│   ├── README.md
│   ├── context/
│   │   ├── database-schema.md
│   │   ├── api-endpoints.md
│   │   └── ui-components.md
│   └── prompts/
│       └── feature-request.md
├── writable/
├── .env
├── README.md
└── CLAUDE.md                    ← File ini
```

---

## 🔐 Autentikasi API

Semua request ke endpoint `/api/*` **WAJIB** menyertakan header:

```
Authorization: Bearer {api_token}
```

Token disimpan di tabel `user_tokens` dan divalidasi oleh `app/Filters/ApiAuthFilter.php`.

---

## 🛠️ Tech Stack

| Komponen | Teknologi |
|----------|-----------|
| **Backend Framework** | CodeIgniter 4 (PHP 8.2+) |
| **Database** | MySQL / MariaDB |
| **Frontend CSS** | Tailwind CSS (CDN) |
| **Frontend JS** | Vanilla JavaScript (ES6+) |
| **Icons** | Google Material Symbols |
| **Fonts** | Google Fonts |
| **Web Server** | Nginx / OpenLiteSpeed (via aaPanel) |
| **Server OS** | AlmaLinux + aaPanel |

---

## 📡 Konvensi API Response

Semua API response menggunakan format standar:

```json
{
  "status": true,
  "code": 200,
  "message": "Success",
  "data": {}
}
```

Error response:
```json
{
  "status": false,
  "code": 401,
  "message": "Unauthorized",
  "data": null
}
```

---

## 📌 Aturan untuk Agents

1. **Jangan ubah** struktur folder tanpa izin eksplisit user
2. **Selalu ikuti** konvensi API response standar di atas
3. **Semua logika bisnis** harus di `Services/`, bukan di `Controllers/`
4. **Controllers** hanya boleh: validasi request → panggil Service → return response
5. **Models** hanya boleh: query database, tidak boleh ada business logic
6. Frontend berkomunikasi ke backend **hanya via API** (tidak boleh langsung ke Model)
7. Gunakan `Google Material Symbols` untuk semua ikon
8. Gunakan `Tailwind CSS` (CDN) — **tidak** menggunakan Bootstrap
9. Semua kode PHP harus **OOP** dan mengikuti PSR-4

---

## 🌐 URL Convention

| Pattern | Keterangan |
|---------|-----------|
| `/` | Halaman utama |
| `/auth/login` | Login |
| `/auth/register` | Register |
| `/dashboard` | Dashboard guru |
| `/viewer/{slug}` | Viewer e-book/presentasi |
| `/api/v1/auth/login` | API login → return token |
| `/api/v1/decks` | API CRUD deck |
| `/api/v1/decks/{id}` | API single deck |
