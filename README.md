# Ketring Yuk — Marketplace Katering

Aplikasi Laravel untuk menghubungkan kantor dengan penyedia katering makan siang.

## Persyaratan

### Menjalankan secara lokal
Panduan ini akan menjelaskan penginstalan dan menjalankan app pertama kali di lokal. 
Untuk penginstalan lebih cepat bisa melalui docker buka file `PANDUAN-DOCKER.md`

### Requirements
- Git.
- PHP 8.2 atau lebih baru dengan ekstensi `curl`, `fileinfo`, `mbstring`, `openssl`, `pdo_mysql`, dan `zip`.
- Composer 2.
- MySQL 8 yang aktif.

### Menjalankan dengan Docker

- Git.
- Docker Desktop atau Docker Engine dengan Docker Compose v2.
- Koneksi internet saat image Docker pertama kali diunduh/dibangun.

### Langkah Menjalankan di lokal 

1. Clone repository. Ganti `<URL-REPOSITORY>` dengan URL repository GitHub proyek:

   ```powershell
   git clone <URL-REPOSITORY> marketplace-katering
   cd marketplace-katering
   ```

2. Buat database MySQL, misalnya lewat HeidiSQL atau MySQL console:

   ```sql
   CREATE DATABASE katering CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. Salin dan atur konfigurasi aplikasi:

   ```powershell
   Copy-Item .env.example .env
   notepad .env
   ```

   Isi `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=katering`, `DB_USERNAME`, dan `DB_PASSWORD` sesuai MySQL lokal.

4. Pasang dependensi, buat kunci Laravel, buat tabel, dan siapkan penyimpanan foto:

   ```powershell
   composer install
   php artisan key:generate
   php artisan migrate
   php artisan storage:link
   ```

5. Jalankan aplikasi:

   ```powershell
   php artisan serve
   ```

   Buka `http://127.0.0.1:8000`.