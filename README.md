# IT Today - Admin Dashboard Portal

Sistem informasi manajemen terintegrasi untuk operasional internal IT Today. Proyek ini dibangun untuk mengakomodasi manajemen akun staff, verifikasi transaksi keuangan peserta, serta pengelolaan kelengkapan data kompetisi secara terpusat.

## 👥 User Roles & Access Control

Sistem ini menerapkan Role-Based Access Control (RBAC) dengan 3 aktor utama:

* **Superadmin:** Pengelola tingkat tertinggi dengan hak akses penuh (CRUD) untuk mengonfigurasi seluruh aspek sistem, data staff internal, dan pengaturan role.
* **Admin Keuangan:** Bertanggung jawab penuh atas validasi keuangan, pemeriksaan berkas bukti transfer, persetujuan/penolakan pembayaran (dilengkapi catatan error), dan pemantauan rekapitulasi dana pendaftaran keseluruhan.
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

# 2. Install dependensi PHP (Vendor)
composer install

# 3. Install dependensi JavaScript & CSS (Node Modules)
npm install

# 4. Setup konfigurasi Environment
cp .env.example .env
# (Catatan untuk pengguna Windows CMD: gunakan perintah `copy .env.example .env`)

# 5. Generate Application Key
php artisan key:generate

# 6. Konfigurasi Database
# Pastikan kamu sudah menyiapkan database di local (misal via phpMyAdmin/XAMPP)
# Sesuaikan nama database di dalam file .env, kemudian jalankan migrasi:
php artisan migrate

# 7. Jalankan Server Development
# Buka dua jendela terminal terpisah dan jalankan kedua perintah ini secara bersamaan:

# Terminal 1: Menjalankan server backend PHP
php artisan serve

# Terminal 2: Menjalankan Vite (Compile Tailwind CSS secara realtime)
npm run dev
