# IT Today - Admin Dashboard Portal

Sistem informasi manajemen terintegrasi untuk operasional internal IT Today. Proyek ini dibangun untuk mengakomodasi manajemen akun staff, verifikasi transaksi keuangan peserta, serta pengelolaan kelengkapan data kompetisi secara terpusat.

## 👥 User Roles & Access Control

Sistem ini menerapkan Role-Based Access Control (RBAC) dengan 3 aktor utama:

* **Superadmin:** Pengelola tingkat tertinggi dengan hak akses penuh (CRUD) untuk mengonfigurasi seluruh aspek sistem, data staff internal, dan pengaturan role.
* **Admin Biasa:** Bertanggung jawab penuh atas validasi keuangan, pemeriksaan berkas bukti transfer, persetujuan/penolakan pembayaran (dilengkapi catatan error), dan pemantauan rekapitulasi dana pendaftaran keseluruhan.
* **Panitia Lomba (Kadiv/Perwakilan):** Pengelola teknis operasional kompetisi. Memiliki hak akses terisolasi untuk memverifikasi berkas identitas peserta (KTM, Twibbon), mengelola lini masa (timeline) lomba, dan menerbitkan pengumuman di dashboard peserta.

## 📊 Use Case Diagram
<img width="1021" height="967" alt="UseCase_Admin" src="https://github.com/user-attachments/assets/9de70cba-7152-457c-b4dc-2c6b8d81f6d5" />


## 🛠️ Tech Stack

* **Framework:** Laravel 12
* **Frontend:** Blade Templating
* **Styling:** Tailwind CSS
* **Interactivity:** Alpine.js

---

## 🚀 Cara Instalasi (Local Development)

Ikuti langkah-langkah berikut untuk menginisialisasi dan menjalankan proyek di komputer masing-masing.

```bash
# 1. Clone repository dari GitHub Organisasi
git clone https://github.com/pusdatin-ittoday/dashboard-ittod-admin.git

# Masuk ke direktori proyek
cd dashboard-ittod-admin
```

# 2. Install dependensi PHP (Vendor)
```bash
composer install
```

# 3. Install dependensi JavaScript & CSS (Node Modules)
```bash
npm install
```

### 4. Setup konfigurasi Environment
Proyek ini memiliki dua template environment:
- `.env.dev` : Digunakan untuk development lokal (menggunakan log mailer, debug true)
- `.env.prod`: Digunakan untuk production (menggunakan SMTP riil, debug false)

Salin file yang sesuai menjadi `.env`, misalnya untuk development:
```bash
cp .env.dev .env
# (Catatan untuk pengguna Windows CMD: gunakan perintah `copy .env.dev .env`)
```

### 5. Konfigurasi Mailer (Opsional)
Buka file `.env` dan sesuaikan bagian `MAIL_` untuk mengatur pengiriman email.
- **Development** (`.env.dev` default): `MAIL_MAILER=log` (email hanya masuk ke file log)
- **Production** (`.env.prod`): Gunakan credentials SMTP riil (misal Gmail, Sendgrid). 

Contoh konfigurasi:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=email_anda@gmail.com
MAIL_PASSWORD=app_password_anda
MAIL_ENCRYPTION=smtps
```

### 5.1. Panduan Testing Email di Local (Development)

**Cara 1: Melihat Email di Log (Default `.env.dev`)**
Jika menggunakan `MAIL_MAILER=log`, Laravel tidak mengirimkan email melainkan menuliskan keseluruhan konten email (termasuk link) ke dalam file log.
1. Jalankan aksi yang memicu pengiriman email (misal: tambah admin).
2. Buka file `storage/logs/laravel.log`.
3. Scroll ke bagian paling bawah, copy link yang ada di sana dan paste di browser.

**Cara 2: Menggunakan Mailtrap (Rekomendasi untuk testing UI)**
Jika ingin melihat tampilan HTML email seperti email asli tanpa mengirim ke pengguna asli, sangat disarankan menggunakan [Mailtrap](https://mailtrap.io).
1. Login ke [Mailtrap.io](https://mailtrap.io) dan masuk ke **Sandboxes**.
2. Klik **Add Sandbox**  dan beri nama.
3. Klik logo "Settings" lalu lihat bagian credentials. Ubah bagian `MAIL_` di `.env` menjadi:
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=username_dari_mailtrap
MAIL_PASSWORD=password_dari_mailtrap
MAIL_ENCRYPTION=tls
```
3. Restart server backend (`php artisan serve`). Email akan masuk ke inbox Mailtrap di browser.

### 6. Generate Application Key & Database
```bash
php artisan key:generate

# Konfigurasi Database
# Pastikan kamu sudah menyiapkan database di local (misal via phpMyAdmin/XAMPP)
# Sesuaikan nama database di dalam file .env, kemudian jalankan migrasi:
php artisan migrate
```

### 7. Jalankan Server Development
Buka dua jendela terminal terpisah dan jalankan kedua perintah ini secara bersamaan:

**Terminal 1 (Backend PHP):**
```bash
php artisan serve
```

**Terminal 2 (Frontend Assets - Tailwind CSS):**
```bash
npm run dev
```
