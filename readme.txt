=== Ringkas ===
Contributors: yasiryas
Tags: shortlink, short link, url shortener, link management, url
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.4.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Buat short link di root domain (domain.com/kode) dengan dashboard minimalis, pencarian instan, dan auto-expire. Tanpa plugin atau layanan pihak ketiga.

== Description ==

Plugin **Ringkas** memungkinkan Anda membuat tautan pendek langsung di root domain (contoh: `domain.com/promo2026`) tanpa perlu layanan short-link eksternal. Semua data tersimpan di database WordPress Anda sendiri.

= Fitur utama =

* **Short link di root domain** — tautan pendek yang bersih seperti `https://domain.com/promo2026`
* **Kelola dari wp-admin** — halaman "Kelola Tautan" dengan antarmuka yang bersih dan mudah dipakai
* **Dashboard front-end** — kelola tautan juga dari `/short/dashboard`
* **Pencarian instan** — filter alias atau URL tujuan secara real-time
* **Auto-expire** — setiap tautan bisa diberi tanggal kedaluwarsa otomatis
* **Statistik realtime** — jumlah tautan, tautan aktif, dan total klik diperbarui otomatis
* **Feedback via email** — pengguna dapat mengirim saran atau laporan bug langsung ke admin lewat email
* **Ringan & cepat** — CSS dan JavaScript kecil, font sistem, tanpa dependensi berat

= Kemudahan penggunaan =

1. Aktifkan plugin lalu buka menu **Ringkas** di wp-admin.
2. Klik **Buat tautan**, masukkan URL tujuan, lalu simpan.
3. Salin tautan pendek yang dihasilkan dan bagikan.
4. Kelola, edit, atau hapus tautan kapan saja.

== Installation ==

1. Unduh plugin dan ekstrak ke folder `wp-content/plugins/ringkas/`, atau unggah berkas `.zip` lewat **Plugins → Add New → Upload Plugin**.
2. Aktifkan plugin melalui menu **Plugins** di wp-admin.
3. Buka menu **Ringkas** untuk mulai membuat tautan pendek.
4. (Opsional) Daftarkan akun front-end lewat `/short/register` untuk mengelola dari `/short/dashboard`.

== Frequently Asked Questions ==

= Bagaimana cara membuat tautan pendek? =

Buka menu **Ringkas** di wp-admin, klik tombol **Buat tautan**, masukkan URL tujuan, lalu klik **Simpan**. Tautan pendek langsung dibuat.

= Di mana tautan pendek di-host? =

Langsung di root domain WordPress Anda, misalnya `https://domain.com/gambar`.

= Bagaimana cara membuat tautan kedaluwarsa? =

Saat membuat atau mengedit tautan, isi kolom **Kedaluwarsa** dengan tanggal dan waktu yang diinginkan. Tautan otomatis dinonaktifkan setelah waktu tersebut.

= Apakah plugin ini memakai layanan pihak ketiga? =

Tidak. Semua tautan dan statistik disimpan di database WordPress Anda sendiri.

= Bagaimana cara memberi feedback? =

Gunakan tombol **Feedback** yang ada di halaman Kelola Tautan (wp-admin atau dashboard front-end). Pesan Anda akan dikirim ke email admin.

== Screenshots ==

1. Halaman Kelola Tautan di wp-admin.
2. Dashboard front-end di /short/dashboard.

== Changelog ==

= 1.4.0 =
* Perombakan tampilan halaman "Kelola Tautan" agar selaras dengan halaman Dokumentasi.
* Penambahan fitur kirim feedback via email.
* Penambahan tautan "Buy me a coffee" dan kredit pengembang.
* Optimasi dan pembersihan CSS agar plugin lebih ringan.
* Pengurangan frekuensi polling statistik untuk meringankan beban server.

== Upgrade Notice ==

= 1.4.0 =
Pembaruan tampilan dan penambahan fitur feedback. Sebaiknya bersihkan cache browser setelah memperbarui.
