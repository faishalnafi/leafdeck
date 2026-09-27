# UI Components — LeafDeck

> Semua komponen menggunakan Tailwind CSS (CDN) + Google Material Symbols + Google Fonts.

---

## 🎨 Design System

### Fonts
```html
<!-- Di semua layout head -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
```

### Color Palette (Tailwind Classes & Hex)
| Peran | Hex / Class | Keterangan |
|-------|-------------|------------|
| **Brand Primary (Google Green)** | `#34A853` (`emerald-600` / `brand`) | Warna utama tombol, logo, dan aksi aktif |
| **Primary Hover / Dark** | `#2d9249` (`emerald-700`) | Hover state tombol |
| **Primary Light / Background** | `#eaf7ee` (`emerald-50`) | Latar belakang notifikasi / badge |
| **Primary Border Light** | `#d1f0db` (`emerald-100`) | Border kartu & info box |
| Secondary | `slate-700`, `slate-800` | Teks sekunder & icon |
| Background | `slate-50`, `white` | Latar halaman & kartu |
| Text | `slate-900`, `slate-600` | Konten teks utama |
| Border | `slate-200` | Garis batas komponen |
| Error | `red-500` | Status kesalahan / gagal |

---

## 📦 Komponen Tersedia

### 1. Button Primary
```html
<button class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-sm">
  <span class="material-symbols-rounded text-[18px]">upload</span>
  Upload Deck
</button>
```

### 2. Button Secondary (Outline)
```html
<button class="inline-flex items-center gap-2 border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium px-5 py-2.5 rounded-xl transition-all duration-200">
  <span class="material-symbols-rounded text-[18px]">edit</span>
  Edit
</button>
```

### 3. Card Deck
```html
<div class="bg-white rounded-2xl border border-slate-200 p-5 hover:shadow-md transition-shadow duration-200">
  <div class="aspect-video bg-slate-100 rounded-xl mb-4 overflow-hidden">
    <img src="..." class="w-full h-full object-cover">
  </div>
  <h3 class="font-semibold text-slate-900 text-sm line-clamp-2">Materi Matematika Kelas 10</h3>
  <p class="text-xs text-slate-500 mt-1">24 Sep 2026 • 142 views</p>
</div>
```

### 4. Alert / Toast
```html
<!-- Success -->
<div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl">
  <span class="material-symbols-rounded text-[20px]">check_circle</span>
  <p class="text-sm font-medium">Deck berhasil diupload!</p>
</div>
<!-- Error -->
<div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
  <span class="material-symbols-rounded text-[20px]">error</span>
  <p class="text-sm font-medium">Terjadi kesalahan.</p>
</div>
```

### 5. Input Field
```html
<div class="space-y-1.5">
  <label class="text-sm font-medium text-slate-700">Judul Deck</label>
  <input type="text"
    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
    placeholder="Masukkan judul materi...">
</div>
```

### 6. Badge
```html
<!-- Public -->
<span class="inline-flex items-center gap-1 bg-emerald-100 text-emerald-700 text-xs font-medium px-2.5 py-1 rounded-full">
  <span class="material-symbols-rounded text-[12px]">public</span> Publik
</span>
<!-- Private -->
<span class="inline-flex items-center gap-1 bg-slate-100 text-slate-600 text-xs font-medium px-2.5 py-1 rounded-full">
  <span class="material-symbols-rounded text-[12px]">lock</span> Privat
</span>
```

---

## 🖼️ Icon Reference (Material Symbols)

| Ikon | Nama Class | Kegunaan |
|------|-----------|---------|
| 📤 | `upload` | Upload file |
| 📚 | `menu_book` | E-book / Deck |
| ✏️ | `edit` | Edit |
| 🗑️ | `delete` | Hapus |
| 👁️ | `visibility` | Lihat / View |
| 🔒 | `lock` | Privat |
| 🌐 | `public` | Publik |
| ⚙️ | `settings` | Pengaturan |
| 👤 | `person` | Profil |
| 🏠 | `home` | Home |
| ➕ | `add` | Tambah |
| ✅ | `check_circle` | Sukses |
| ❌ | `cancel` | Tutup/Batal |
| ⚠️ | `error` | Error |

```html
<!-- Cara pakai icon -->
<span class="material-symbols-rounded">icon_name</span>

<!-- Dengan ukuran custom -->
<span class="material-symbols-rounded text-[20px]">upload</span>
```
