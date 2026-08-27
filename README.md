# Ringkas

Plugin **short-link WordPress** buatan [Yasyes Studio](https://yasyes.id) yang membuat tautan pendek langsung di root domain (`domain.com/kode`) tanpa layanan pihak ketiga. Dilengkapi dashboard minimalis untuk mengelola tautan.

## Fitur

- **Short link di root domain** — tautan pendek seperti `domain.com/promo2026`
- **Kelola dari wp-admin** — menu "Kelola Tautan" dengan UI bersih
- **Dashboard front-end** — kelola juga dari `/short/dashboard`
- **Pencarian instan** — filter alias/URL tujuan secara real-time
- **Auto-expire** — tautan bisa diberi tanggal kedaluwarsa
- **Statistik realtime** — jumlah tautan, tautan aktif, dan total klik dengan polling otomatis
- **Feedback via email** — pengguna bisa kirim saran/bug langsung ke admin
- **Ringan & cepat** — CSS/JS kecil, font system, tanpa dependensi berat

## Instalasi

1. Unduh plugin dan ekstrak ke folder `wp-content/plugins/ringkas/`
2. Aktifkan plugin lewat **Plugins → Installed Plugins**
3. Daftarkan akun lewat `/short/register` atau gunakan menu Ringkas di wp-admin

## Penggunaan

1. **Buat tautan** — klik "Buat tautan", masukkan URL tujuan, lalu simpan
2. **Salin tautan pendek** — klik ikon salin di kolom Aksi
3. **Edit / hapus** — gunakan ikon di kolom Aksi tiap baris
4. **Cari** — ketik di kolom pencarian untuk memfilter daftar
5. **Kedaluwarsa** — set tanggal kedaluwarsa saat membuat/editing tautan

## Persyaratan

| | Minimum |
|---|---|
| WordPress | 6.0+ |
| PHP | 7.4+ |

## Struktur
```
ringkas/
├── ringkas.php              # Bootstrap & metadata plugin
├── includes/                # Logic (model, service, AJAX, routing, admin)
├── templates/               # Template admin & front-end
└── assets/                  # ringkas.css & ringkas.js
```

## Lisensi

GPL v2 or later.

---

Dibuat dengan ☕ oleh [Yasyes Studio](https://yasyes.id) — dukung lewat [Buy Me A Coffee](https://buymeacoffee.com/yasir123983?utm_source=ringkas&utm_campaign=github).
