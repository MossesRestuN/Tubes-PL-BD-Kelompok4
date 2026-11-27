# sisteminformasi_kedai_rutinitas_kelompok4
tubes besar pl & basdat kel4
# Rutinitas Coffee, Sistem Operasional Internal (PHP MVC + PDO)

## Cara menjalankan (Laragon)
1. Salin folder ini ke `C:\laragon\www\rutinitas_coffee`
2. Jalankan seluruh isi `rutinitas_coffee.sql` 
3. Buka `http://localhost/rutinitas_coffee/public/`

## Akun contoh
| Peran    | Username | Password    |
|----------|----------|-------------|
| Owner    | owner    | owner123    |
| Manajer  | manajer  | manajer123  |
| Karyawan | karyawan | karyawan123 |

## Stok bahan baku
- Stok tidak dikurangi otomatis dari penjualan (tidak ada resep, dan mitra tidak menghitung selisih secara spesifik).
- Semua peran dapat mencatat **Ambil** (bahan di kitchen habis, diambil dari gudang) dan **Tambah** (stok masuk) di menu Bahan Baku. Nama petugas, jumlah, stok sebelum dan sesudah, serta keterangan tersimpan di tabel `riwayat_stok`.
- Stok tidak bisa diedit langsung lewat form Ubah, hanya lewat Ambil dan Tambah, supaya semua perubahan tercatat.
- **Pilih tanggal**: saat Ambil atau Tambah, tanggal bisa diubah (tidak boleh lewat dari hari ini) kalau kemarin lupa mencatat.
- **Status**: Aman, Menipis (stok <= minimum), dan Habis (stok 0).
- **Kadaluarsa**: setiap stok masuk menjadi satu batch yang disimpan di `riwayat_stok` (kolom `sisa` dan `tanggal_kadaluarsa`), dengan tanggal kadaluarsa sendiri. Saat stok baru masuk, batch lama yang sudah kadaluarsa pada tanggal itu dibuang otomatis dan tercatat di riwayat sebagai "Kadaluarsa dibuang". Pengambilan memakai batch yang paling dulu kadaluarsa (FEFO).
- **Menu**: status Tersedia atau Kosong; menu Kosong tidak muncul di form Pesanan Baru dan ditolak server.
- **Pesanan**: rincian tampil sebagai jendela mengambang di daftar pesanan (`pesanan/rincian/ID` mengembalikan JSON).
- **Laporan**: rekap per hari, menu terlaris, dan kinerja pengguna menampilkan 3 baris teratas, sisanya lewat "Lihat semua".
- Database baru (6 tabel: pengguna, menu, bahan_baku, pesanan, detail_pesanan, riwayat_stok): cukup `rutinitas_coffee.sql`. Database lama: aplikasi menyesuaikan sendiri saat login (`app/core/Skema.php`), atau jalankan `database/migrasi_gabung_batch_ke_riwayat.sql` sekali.
- Ambil dan Tambah stok dikirim dari jendela di daftar bahan. Jika input salah, pesan galat tampil di bagian atas halaman.

## Struktur
- `public/index.php` front controller dan router (`?url=controller/action/param`)
- `app/core` Database (PDO + transaksi), QueryBuilder, Validator, Auth, Model, Controller, CrudController
- `app/models`, `app/controllers`, `app/views`
- `config/config.php` kredensial database
