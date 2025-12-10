# Talent Pool Analytics Dashboard

Aplikasi monitoring skill dan prestasi mahasiswa berbasis Laravel.

## Fitur
- Import Data CSV Otomatis.
- Visualisasi Grafik (Chart.js).
- Filter Data (Fakultas, Jurusan, Angkatan).
- Komparasi Antar Jurusan & Fakultas.

## Cara Install & Menjalankan (Untuk Orang Lain)

Ikuti langkah ini untuk menjalankan project di komputer Anda:

1. **Clone Repository**
   ```bash
   git clone [https://github.com/USERNAME_ANDA/talent-pool-laravel.git](https://github.com/USERNAME_ANDA/talent-pool-laravel.git)
   cd talent-pool-laravel
Install Dependencies (Pastikan sudah install PHP dan Composer)

Bash

composer install
Setting Environment

Copy file .env.example menjadi .env.

Buka file .env, atur nama database:

Plaintext

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=talent_pool_db
DB_USERNAME=root
DB_PASSWORD=
Generate Key

Bash

php artisan key:generate
Buat Database

Buka phpMyAdmin, buat database baru bernama talent_pool_db.

Migrasi & Seeding Data Pastikan file CSV (Tracking Data Skill Mahasiswa - Tracking.csv) sudah ada di folder root project.

Bash

php artisan migrate:fresh --seed --class=TalentSeeder
Jalankan Server

Bash

php artisan serve
Buka browser di http://127.0.0.1:8000.

Catatan
Jika ingin mengupdate data, gunakan fitur "Import Data Baru" di dashboard (tombol hijau di pojok kanan atas).


---

### TAHAP 5: Update Terakhir

Setelah mengedit `README.md` di atas, jangan lupa upload lagi perubahannya:

```bash
git add README.md
git commit -m "Update panduan instalasi"
git push
