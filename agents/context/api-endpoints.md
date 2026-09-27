# API Endpoints — LeafDeck

> Update file ini setiap ada endpoint baru atau perubahan.

---

## Base URL

```
Production : https://yourdomain.com/api/v1
Development: http://localhost:8080/api/v1
```

## Auth Header (wajib untuk semua endpoint bertanda 🔒)

```
Authorization: Bearer {token}
Content-Type: application/json
```

---

## 🔓 Auth Endpoints (Publik)

### POST `/auth/login`
Login dan dapatkan Bearer token.

**Request:**
```json
{
  "email": "guru@leafdeck.id",
  "password": "password123"
}
```

**Response 200:**
```json
{
  "status": true,
  "code": 200,
  "message": "Login berhasil",
  "data": {
    "token": "eyJ...",
    "expires_at": "2026-09-28T20:00:00+07:00",
    "user": {
      "id": 1,
      "name": "Pak Budi",
      "email": "guru@leafdeck.id",
      "role": "teacher"
    }
  }
}
```

---

### POST `/auth/logout` 🔒
Logout dan hapus token.

**Response 200:**
```json
{
  "status": true,
  "code": 200,
  "message": "Logout berhasil",
  "data": null
}
```

---

## 📚 Deck Endpoints

### GET `/decks` 🔒
Ambil semua deck milik user yang login.

**Response 200:**
```json
{
  "status": true,
  "code": 200,
  "message": "Success",
  "data": {
    "decks": [...],
    "total": 10,
    "page": 1,
    "per_page": 10
  }
}
```

---

### POST `/decks` 🔒
Upload deck baru (HTML file).

**Request:** `multipart/form-data`
```
title       : string (required)
description : string (optional)
file        : file HTML (required, max 10MB)
is_public   : boolean (optional, default: false)
```

**Response 201:**
```json
{
  "status": true,
  "code": 201,
  "message": "Deck berhasil diupload",
  "data": {
    "id": 5,
    "slug": "materi-matematika-kelas-10"
  }
}
```

---

### GET `/decks/{id}` 🔒
Detail satu deck.

---

### PUT `/decks/{id}` 🔒
Update metadata deck (judul, deskripsi, visibilitas).

---

### DELETE `/decks/{id}` 🔒
Hapus deck (soft delete).

---

## 👤 User Endpoints

### GET `/users/profile` 🔒
Ambil profil user yang sedang login.

### PUT `/users/profile` 🔒
Update profil (nama, avatar).

### PUT `/users/password` 🔒
Ganti password.

---

## ⚠️ Error Codes

| Code | Arti |
|------|------|
| 400 | Bad Request (validasi gagal) |
| 401 | Unauthorized (token tidak ada/invalid) |
| 403 | Forbidden (bukan pemilik resource) |
| 404 | Not Found |
| 422 | Unprocessable Entity |
| 500 | Internal Server Error |
