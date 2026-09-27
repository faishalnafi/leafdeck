# Database Schema — LeafDeck

> Update file ini setiap ada migrasi database baru.

---

## ERD Overview

```
users ──────────< user_tokens
  │
  └──────────< decks
                 │
                 └──────────< deck_views
```

---

## Tabel: `users`

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| `id` | INT UNSIGNED PK AI | Primary key |
| `name` | VARCHAR(100) | Nama lengkap |
| `email` | VARCHAR(150) UNIQUE | Email login |
| `password` | VARCHAR(255) | Hashed password (bcrypt) |
| `role` | ENUM('admin','teacher') | Role pengguna |
| `avatar` | VARCHAR(255) NULL | Path foto profil |
| `is_active` | TINYINT(1) DEFAULT 1 | Status aktif |
| `created_at` | DATETIME | Waktu buat |
| `updated_at` | DATETIME | Waktu update |

---

## Tabel: `user_tokens`

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| `id` | INT UNSIGNED PK AI | Primary key |
| `user_id` | INT UNSIGNED FK | Relasi ke users |
| `token` | VARCHAR(255) UNIQUE | Bearer token |
| `expires_at` | DATETIME | Waktu kedaluwarsa |
| `created_at` | DATETIME | Waktu buat |

---

## Tabel: `decks`

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| `id` | INT UNSIGNED PK AI | Primary key |
| `user_id` | INT UNSIGNED FK | Pemilik deck |
| `title` | VARCHAR(200) | Judul deck |
| `slug` | VARCHAR(220) UNIQUE | URL-friendly identifier |
| `description` | TEXT NULL | Deskripsi singkat |
| `file_path` | VARCHAR(500) | Path file HTML yang diupload |
| `thumbnail` | VARCHAR(500) NULL | Path thumbnail |
| `is_public` | TINYINT(1) DEFAULT 0 | Apakah bisa diakses publik |
| `view_count` | INT UNSIGNED DEFAULT 0 | Jumlah penayangan |
| `created_at` | DATETIME | Waktu buat |
| `updated_at` | DATETIME | Waktu update |
| `deleted_at` | DATETIME NULL | Soft delete |

---

## Tabel: `deck_views`

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| `id` | INT UNSIGNED PK AI | Primary key |
| `deck_id` | INT UNSIGNED FK | Relasi ke decks |
| `viewer_ip` | VARCHAR(45) | IP viewer |
| `viewed_at` | DATETIME | Waktu lihat |
