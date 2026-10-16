### Melakukan Ekspor database dari VM lama (OPTIONAL)
`karena backup.sql sudah ada. langkah ini bisa di skip. Langsung ke clone github`

Pastikan MySQL sumber aktif dan `.env` berisi konfigurasi database sumber yang benar. Dari folder proyek, jalankan:

```powershell
.\docker\ekspor-database.ps1
```

Skrip tersebut membuat atau mengganti `backup.sql` di folder utama proyek. File lama hanya diganti jika ekspor baru berhasil. Salin file `backup.sql` ke VM tujuan secara terpisah; jangan commit file backup ke Git.


## Menjalankan dengan Docker dari GitHub
### 1. Clone proyek pada VM tujuan dan siapkan konfigurasi Docker

```powershell
git clone https://github.com/aqilfikri20/marketplace-katering
cd marketplace-katering
Copy-Item .env.docker.example .env.docker
notepad .env.docker
```

Isi `DOCKER_APP_KEY`, `DOCKER_DB_PASSWORD`, dan `DOCKER_DB_ROOT_PASSWORD` dengan nilai yang sesuai dan kuat. `DOCKER_DB_DATABASE` serta `DOCKER_DB_USERNAME` juga harus terisi. Jangan menimpa `.env` lokal; Docker menggunakan `.env.docker`.

### 2. Build image Docker

```powershell
docker compose --env-file .env.docker build
```

### 3. Jalankan layanan Docker

```powershell
docker compose --env-file .env.docker up -d
```

Tunggu sampai MySQL sehat, lalu hentikan aplikasi sementara agar tidak mengakses database saat impor:

```powershell
docker compose --env-file .env.docker ps
docker compose --env-file .env.docker stop app
```

### 4. Impor `backup.sql` ke MySQL Docker

Pastikan `backup.sql` berada di folder utama proyek. Jalankan dari PowerShell:

```powershell
$dbContainer = docker compose --env-file .env.docker ps -q db
docker cp .\backup.sql "${dbContainer}:/tmp/backup.sql"
docker compose --env-file .env.docker exec -T db sh -c 'mysql --default-character-set=utf8mb4 -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE" < /tmp/backup.sql'
docker compose --env-file .env.docker exec -T db rm -f /tmp/backup.sql
```

Dump dibuat dengan perintah `mysqldump` yang menyertakan `DROP TABLE`; impor mengganti tabel bernama sama di database Docker. Database sumber tidak berubah. Pastikan database Docker tujuan memang boleh ditimpa.

### 5. Jalankan kembali aplikasi

```powershell
docker compose --env-file .env.docker up -d --force-recreate app
```

Aplikasi menjalankan migration Laravel saat mulai. 
Buka `http://127.0.0.1:8081`. 

### Cek status migration dan log bila diperlukan:

```powershell
docker compose --env-file .env.docker exec app php artisan migrate:status
docker compose --env-file .env.docker logs app
```

Data Docker disimpan dalam named volume. Untuk menghentikan layanan tanpa menghapus data:

```powershell
docker compose --env-file .env.docker down
```

Jangan gunakan `down -v` kecuali memang ingin menghapus volume database dan file Docker.
