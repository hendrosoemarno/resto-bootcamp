# Panduan Deployment Laravel ke Shared Hosting (cPanel)

Panduan ini ditujukan untuk mengupload aplikasi restoran ke hosting berbasis cPanel.

## 1. Persiapan File (Di Komputer Lokal)

1. **Bersihkan Cache**:
   Buka terminal di folder project dan jalankan:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

2. **Zip Project**:
   Compress/Zip seluruh isi folder `c:\laragon\www\resto` menjadi satu file `resto.zip`.
   *Kecuali*: Folder `node_modules` (tidak perlu karena kita sudah build assetnya) dan folder `.git` (opsional).
   *Pastikan*: Folder `vendor` ikut terbawa (karena di shared hosting kita biasanya tidak bisa `composer install`).

## 2. Persiapan Database (Di Hosting)

1. Login ke **cPanel**.
2. Buka menu **MySQL Databases**.
3. Buat Database Baru (misal: `u12345_resto`).
4. Buat User Database Baru (misal: `u12345_admin`) dan Passwordnya.
5. **Add User to Database**: Hubungkan user ke database dan centang "ALL PRIVILEGES".
6. Buka **phpMyAdmin** di cPanel, pilih database baru tadi, lalu **Import** file `resto_production.sql` (yang Anda export dari lokal).

## 3. Upload File (Struktur Aman)

Kita tidak akan menaruh semua file di `public_html` karena itu tidak aman (orang bisa download `.env` Anda).

1. Buka **File Manager** di cPanel.
2. Di folder **Root** (sejajar dengan `public_html`, BUKAN di dalamnya), buat folder baru bernama `resto-app`.
3. **Upload** `resto.zip` ke dalam folder `resto-app` tersebut dan **Extract**.
   - Sekarang Anda punya struktur: `/home/user/resto-app/...`

4. **Pindahkan folder Public**:
   - Masuk ke folder `/home/user/resto-app/public`.
   - Pilih semua isinya (pindahkan Select All), lalu **Move (Pindahkan)** ke folder `/home/user/public_html`.
   *(Jika Anda ingin websitenya di subfolder seperti `domain.com/resto`, pindahkan ke `public_html/resto`)*.

## 4. Konfigurasi `index.php`

Karena kita memisahkan file inti dan file publik, kita perlu memberitahu Laravel dimana lokasinya.

1. Buka `/home/user/public_html/index.php` (file index yang baru saja dipindahkan).
2. Edit baris berikut:

   **Cari:**
   ```php
   require __DIR__.'/../storage/autoload.php';
   ...
   $app = require_once __DIR__.'/../bootstrap/app.php';
   ```

   **Ubah Menjadi (sesuaikan path folder resto-app tadi):**
   ```php
   require __DIR__.'/../resto-app/vendor/autoload.php';
   ...
   $app = require_once __DIR__.'/../resto-app/bootstrap/app.php';
   ```
   *(Catatan: `..` artinya naik satu folder. Jika `resto-app` sejajar dengan `public_html`, path ini sudah benar).*

## 5. Konfigurasi `.env`

1. Masuk ke folder `/home/user/resto-app/`.
2. Edit file `.env`.
3. Sesuaikan konfigurasi berikut:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://domain-anda.com

   DB_DATABASE=u12345_resto
   DB_USERNAME=u12345_admin
   DB_PASSWORD=password_anda
   ```

## 6. Storage Link (Penting untuk Gambar)

Di shared hosting, kita sering tidak bisa menjalankan command `php artisan storage:link`.
Solusinya:

1. Buka **File Manager**.
2. Buka folder `resto-app/storage/app`.
3. Apakah ada folder `public`? Jika ya, itu berisi gambar-gambar Anda.
4. Kita perlu menghubungkan folder ini agar bisa diakses browser.
5. **Cara Alternatif (Cron Job)**:
   Menu cPanel -> Cron Jobs -> Add New.
   Command: `ln -s /home/user/resto-app/storage/app/public /home/user/public_html/storage`
   Jalankan sekali saja, lalu hapus cron jobnya.

Atau buat file PHP `link.php` di `public_html` berisi:
```php
<?php
symlink('/home/user/resto-app/storage/app/public', '/home/user/public_html/storage');
echo "Done";
?>
```
Buka `domain.com/link.php` sekali, lalu hapus filenya.

## Selesai!

Website Anda sekarang seharusnya sudah bisa diakses.
