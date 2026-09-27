# Feature Request Template — LeafDeck

Gunakan template ini ketika meminta agent untuk mengimplementasikan fitur baru.

---

## Template

```
## Fitur: [Nama Fitur]

### Deskripsi
[Jelaskan fitur yang ingin dibuat]

### Layer yang Terlibat
- [ ] Model (tabel baru / perubahan schema)
- [ ] Service (logic bisnis)
- [ ] API Controller (endpoint baru)
- [ ] Web Controller (halaman baru)
- [ ] View (UI baru)
- [ ] JS (interaksi frontend)

### Endpoint API (jika ada)
- Method: GET / POST / PUT / DELETE
- URL: /api/v1/...
- Auth: Ya / Tidak

### UI yang Diharapkan
[Deskripsikan tampilan yang diinginkan]

### Acceptance Criteria
- [ ] ...
- [ ] ...

### Catatan Tambahan
[Hal-hal penting lainnya]
```

---

## Contoh

```
## Fitur: Share Deck via Link Publik

### Deskripsi
Guru dapat membuat link publik untuk deck tertentu sehingga
siswa bisa mengakses viewer tanpa login.

### Layer yang Terlibat
- [ ] Model (tambah kolom share_token di tabel decks)
- [x] Service (generate unique share token)
- [x] API Controller (POST /api/v1/decks/{id}/share)
- [ ] Web Controller
- [x] View (tombol share di dashboard)
- [x] JS (copy link ke clipboard)

### Endpoint API
- Method: POST
- URL: /api/v1/decks/{id}/share
- Auth: Ya (Bearer Token)

### UI yang Diharapkan
Tombol "Bagikan" di card deck, ketika diklik muncul modal
dengan link yang bisa di-copy ke clipboard.

### Acceptance Criteria
- [ ] Token share unik per deck
- [ ] Link bisa diakses tanpa login jika deck is_public = true
- [ ] Copy to clipboard dengan feedback visual
```
