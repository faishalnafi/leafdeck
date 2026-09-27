# LeafDeck 🍃

> **L**ayanan **E**dukasi **A**ntarmuka **F**leksibel — **D**istribusi **E**-book **C**erdas untuk **K**elas

*Unggah, Tampilkan, Inspirasi.*

---

## 📖 Tentang LeafDeck

**LeafDeck** adalah platform presentasi berbasis web yang memungkinkan pendidik mengunggah materi HTML interaktif dan menampilkannya sebagai e-book / slide deck digital yang elegan, responsif, dan mudah diakses. Dilengkapi dengan sistem otentikasi Single Sign-On (SSO) Kredensia & Google OAuth, panel administrasi multi-role, dan wizard pemasang web otomatis (*Easy Installer*).

### ✨ Fitur Utama

- 📤 **Upload & Manajemen Materi** — Guru upload slide presentasi HTML interaktif
- 📚 **E-Book Viewer** — Tampilan dokumen dan slide yang interaktif, fullscreen, dan responsif
- 🔐 **SSO Terintegrasi & Google OAuth** — Autentikasi terpusat (Kredensia SSO Sekolah & Google Sign-In)
- 👑 **Role-Based Access Control (RBAC)**:
  - **Superadmin**: Kontrol penuh sistem, manajemen konfigurasi SSO Sekolah & Google OAuth, audit log, manajemen admin & user.
  - **Admin**: Moderasi materi pembelajaran, validasi konten, dan manajemen akun pengguna.
  - **Teacher / User**: Unggah, edit, hapus, dan bagikan materi ajar interaktif.
- 🧙‍♂️ **Web UI Installer (`/install`)** — Setup 1-klik yang memeriksa prasyarat server, konfigurasi database MariaDB/MySQL, migrasi & seeder otomatis, dan konfigurasi awal instansi.
- 📱 **Mobile & Desktop Optimized** — Tampilan modern menggunakan Tailwind CSS.

---

## 🛠️ Tech Stack

| Komponen | Teknologi | Versi |
|----------|-----------|-------|
| Backend | CodeIgniter 4 | 4.x |
| PHP | PHP | 8.2+ |
| Database | MySQL / MariaDB | 10.x / 8.0+ |
| CSS | Tailwind CSS | CDN |
| JavaScript | Vanilla JS | ES6+ |
| Icons | Google Material Symbols & Brand Icon | Latest |
| Fonts | Inter & Plus Jakarta Sans | Google Fonts |

---

## 🏗️ Arsitektur

LeafDeck menggunakan **Layered MVC Monolith Architecture**:

```
[Views + JS]  →(Bearer Token / Session)→  [Controllers]  →  [Services]  →  [Models]  →  [Database]
```

---

## 🚀 Instalasi & Setup

### Cara 1: Menggunakan Web UI Easy Installer (Direkomendasikan)
1. Buka browser dan arahkan ke:
   ```
   http://your-domain.test/install
   ```
2. Ikuti 3 langkah mudah:
   - **Langkah 1**: Pengecekan versi PHP, ekstensi (`intl`, `mbstring`, `mysqli`, `curl`), dan izin tulis direktori (`writable/`, `public/uploads/`).
   - **Langkah 2**: Konfigurasi koneksi database MariaDB / MySQL.
   - **Langkah 3**: Konfigurasi akun Super Admin dan Pengaturan SSO.
3. Klik **Mulai Pemasangan Otomatis**. LeafDeck akan otomatis menyiapkan skema database, tabel, migrasi, dan seeder awal.

---

### Cara 2: Instalasi Manual CLI

**1. Clone project**
```bash
git clone https://github.com/faishalnafi/leafdeck.git
cd leafdeck
```

**2. Install dependencies**
```bash
composer install --no-dev --optimize-autoloader
```

**3. Konfigurasi environment**
```bash
cp env .env
```

Sesuaikan parameter di `.env`:
```ini
CI_ENVIRONMENT = production

app.baseURL = 'https://yourdomain.com/'
app.appTimezone = 'Asia/Jakarta'

database.default.hostname = localhost
database.default.database = leafdeck_db
database.default.username = your_db_user
database.default.password = your_db_password
database.default.DBDriver = MySQLi
```

**4. Jalankan migrasi dan seeder**
```bash
php spark migrate
php spark db:seed DatabaseSeeder
```

**5. Permission direktori**
```bash
chmod -R 777 writable/
chmod -R 755 public/uploads/
```

---

## 🔐 Akun Default Awal

Setelah menjalankan migrasi atau Web Installer, akun bawaan yang tersedia:

| Role | Email / Identifier | Password | Hak Akses |
|------|-------------------|----------|-----------|
| **Super Admin** | `admin@leafdeck.test` | `admin123` | Akses Penuh + Pengaturan SSO Sekolah & Google |
| **Admin** | *(Dapat dibuat oleh Superadmin)* | - | Moderasi Konten & Manajemen Pengguna |
| **Guru / Pengguna** | `guru@leafdeck.test` | `password` | Manajemen Materi Presentasi & Viewer |

---

## 🌐 Rute Halaman Utama

| Path | Deskripsi | Hak Akses |
|------|-----------|-----------|
| `/` | Landing page dan katalog materi | Publik |
| `/auth/login` | Halaman login (Lokal, Kredensia SSO, Google) | Publik / Tamu |
| `/dashboard` | Dashboard materi ajar & upload slide | Pengguna / Guru |
| `/viewer/{slug}` | Pembaca E-Book / Slide Deck Interaktif | Publik / Terproteksi |
| `/admin` | Panel Moderasi & Manajemen User | Admin & Superadmin |
| `/admin` (Tab SSO) | Konfigurasi SSO Sekolah & Google OAuth | Khusus Superadmin |
| `/install` | Web UI Pemasang Cepat (Easy Installer) | Administrator Setup |

---

## 📄 Lisensi

MIT License © 2026 Faishal Nafi'
