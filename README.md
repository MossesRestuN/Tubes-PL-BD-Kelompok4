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
- Database lama: jalankan `database/migrasi_riwayat_stok.sql`. Database baru: cukup `rutinitas_coffee.sql`.

## Struktur
- `public/index.php` front controller dan router (`?url=controller/action/param`)
- `app/core` Database (PDO), QueryBuilder, Validator, Auth, Model, Controller, CrudController
- `app/models`, `app/controllers`, `app/views`
- `config/config.php` kredensial database
