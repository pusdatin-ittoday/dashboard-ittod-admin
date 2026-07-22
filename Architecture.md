# Architecture

Dokumen ini merangkum implementasi Admin Dashboard Portal IT Today berdasarkan use case multi-role: Superadmin, Admin Biasa, dan Panitia Lomba.

## Ringkasan Sistem

Aplikasi adalah dashboard admin Laravel dengan Blade, Alpine.js, dan Tailwind CSS. Auth menggunakan model `App\Models\UserIdentity` sebagai authenticatable user, sedangkan profil pengguna berada di `App\Models\User`.

Modul utama:

- Manajemen akun staff
- Verifikasi pembayaran tim
- Rekap transaksi
- Kelola data dan berkas tim
- Kelola timeline kompetisi dan agenda seminar
- Publish pengumuman dashboard
- Export rekapitulasi CSV

## Role Dan Use Case

| Use Case | Superadmin | Admin Biasa | Panitia Lomba |
| --- | --- | --- | --- |
| UC-01 Kelola Akun & Data Staff | Ya | Tidak | Tidak |
| UC-02 Verifikasi Pembayaran Tim | Ya | Ya | Tidak |
| UC-03 Lihat Rekap Transaksi | Ya | Ya | Tidak |
| UC-04 Kelola Data & Berkas Tim | Ya | Tidak | Ya |
| UC-05 Kelola Lini Masa & Event | Ya | Ya | Ya |
| UC-06 Publish Pengumuman Dashboard | Ya | Ya | Ya |
| UC-07 Ekspor Rekapitulasi Data CSV | Ya | Ya | Ya |
| UC-08 Input Alasan Penolakan | Ya | Ya untuk transaksi, Panitia Lomba untuk berkas | Ya untuk berkas |
| UC-09 Login Multi-Role | Ya | Ya | Ya |
| UC-10 Verifikasi Event Non-Kompetisi | Ya | Ya | Tidak |
| UC-11 List Peserta | Ya (Semua) | Ya (Semua) | Ya (Hanya event ditugaskan) |

## Auth Dan Role

Role disimpan di tabel `user_identity.role` dengan nilai:

- `superadmin`
- `admin_biasa`
- `panitia_lomba`
- `user`

Model auth:

- `App\Models\UserIdentity`
- Provider auth Laravel diarahkan ke `App\Models\UserIdentity` di `config/auth.php`.
- `UserIdentity` memiliki relasi `user()` ke profil `App\Models\User`.
- `UserIdentity` memiliki relasi `events()` ke event yang ditugaskan melalui tabel pivot `event_staff`.

## Routing

File utama: `routes/web.php`.

Route utama:

- `/dashboard`: dashboard staff yang sudah login dan verified.
- `/admin/staff`: manajemen staff, dikunci di controller untuk superadmin.
- `/admin/transactions`: verifikasi pembayaran tim (kompetisi) untuk superadmin dan admin biasa.
- `/admin/event-participants`: verifikasi bukti bayar event non-kompetisi untuk superadmin dan admin biasa.
- `/admin/users`: direktori/list peserta (pengguna). Superadmin & Admin Biasa melihat semua, Panitia Lomba melihat sesuai event.
- `/operation/teams`: kelola data dan berkas tim untuk superadmin dan panitia_lomba.
- `/admin/timelines`: daftar timeline kompetisi dan event untuk superadmin, admin biasa, dan panitia_lomba.
- `/admin/timelines/{event}/agenda`: agenda kegiatan untuk superadmin, admin biasa, dan panitia_lomba yang ditugaskan.
- `/admin/announcements`: pengumuman untuk semua role staff.
- `/export/*`: export CSV, dikunci di `ExportController`.

Catatan: route group menggunakan `auth` dan `verified` pada area admin, sedangkan pembatasan role mayoritas dilakukan di controller dengan `abort_unless(...)`.

## Controller

### `AdminDashboardController`

Tanggung jawab:

- Dashboard admin.
- Manajemen staff.
- Transaksi admin panel.
- Direktori file dan peserta.
- Timeline kompetisi.
- Pengumuman.

Pembatasan akses penting:

- `staff()`, `storeStaff()`, `showStaff()`, `updateStaff()`, `destroyStaff()` hanya untuk superadmin.
- `transactions()`, `acceptTransaction()`, `rejectTransaction()` hanya untuk superadmin dan admin biasa.
- `filesParticipants()`, `files()` hanya untuk superadmin dan panitia_lomba.
- `storeCompetition()`, `updateCompetition()`, `destroyCompetition()`, `toggleCompetitionStatus()` dapat diakses superadmin (seluruh event) dan admin biasa (hanya event `non_competition`).
- `announcements()`, `storeAnnouncement()`, `updateAnnouncement()`, `destroyAnnouncement()` untuk semua staff, dengan panitia_lomba dibatasi hanya event yang ditugaskan. Pengumuman "Umum" (event_id null) hanya bisa dibuat oleh superadmin dan admin biasa.

### `Operation\TeamController`

Tanggung jawab:

- Daftar tim.
- Detail tim dan berkas.
- Verifikasi berkas tim.
- Catatan verifikasi dokumen anggota.

Pembatasan akses:

- `index()` dan `show()` hanya untuk superadmin dan panitia_lomba.
- `updateStatus()` dan `updateMemberStatus()` hanya untuk superadmin dan panitia_lomba.
- Panitia Lomba hanya bisa mengakses tim dari event yang ditugaskan lewat `event_staff`.

### `Operation\TimelineController`

Tanggung jawab:

- CRUD timeline kegiatan non-kompetisi atau agenda seminar.
- Akses untuk superadmin, admin biasa, dan panitia_lomba.
- Panitia Lomba hanya bisa mengelola event yang ditugaskan.

### `Admin\EventParticipantController`

Tanggung jawab:

- Endpoint verifikasi pembayaran peserta event non-kompetisi.
- Filter berdasarkan status verifikasi dan event.
- Status yang sudah diterima (accepted) akan dikunci (tidak bisa diubah lagi).

### `TransactionController`

Tanggung jawab:

- Endpoint JSON verifikasi transaksi.
- Endpoint JSON rekap transaksi.

Pembatasan akses:

- `verify()` hanya untuk superadmin dan admin biasa.
- `getRecap()` hanya untuk superadmin dan admin biasa.
- Reject transaksi mewajibkan `verification_error`.

### `ExportController`

Tanggung jawab:

- Export rekap tim per event.
- Export rekap peserta per event.
- Export rekap tim global.
- Export rekap peserta global.

Pembatasan akses:

- Per-event: superadmin, admin biasa, dan panitia_lomba.
- Panitia Lomba hanya bisa export event yang ditugaskan.
- Global: superadmin dan admin biasa.

## Model Dan Relasi

### `UserIdentity`

Tabel: `user_identity`

Relasi:

- `user()`: profil user.
- `events()`: event yang ditugaskan ke staff melalui `event_staff`.

### `User`

Tabel: `user`

Relasi:

- `identity()`
- `media()`
- `teams()`
- `events()`

Field penting:

- `last_read_announcements_at`: Waktu (timestamp) kapan terakhir kali pengguna (peserta) mengakses halaman pengumuman. Digunakan oleh *frontend* Node/React untuk memicu notifikasi visual (titik merah/red dot) jika terdapat pengumuman baru.

### `Event`

Tabel: `event`

Relasi:

- `teams()`
- `timelines()`
- `announcements()`
- `participants()`
- `staff()`

Field penting:

- `logo_url`: URL gambar logo event (disimpan di S3/R2).
- `whatsapp_group_link`: Tautan grup WA/Discord untuk peserta. Nama field tetap dipertahankan demi kompatibilitas database/API.
- `contact_person1`: Nomor CP 1 (disimpan dalam format angka saja).
- `contact_person2`: Nomor CP 2 (disimpan dalam format angka saja).
- `participation_type`: Tipe pendaftaran event dengan nilai `individual` atau
  `team`. Nilai dipilih saat event dibuat atau diedit dan digunakan web API
  untuk menentukan alur pendaftaran peserta.
- `method`: Metode pelaksanaan event (`online` atau `offline`). Otomatis `offline` untuk kompetisi, dapat diset manual untuk non-kompetisi.
- `max_noncompetition_participant`: Batas maksimal peserta (opsional) untuk event non-kompetisi.
- `price`: Biaya pendaftaran (otomatis 0 untuk event non-kompetisi).
- `submission_fields`: Konfigurasi JSON untuk menyimpan format/kolom isian submission karya lomba.
- `external_platform_link`: URL platform eksternal untuk kompetisi yang tidak memerlukan submission internal (`requires_submission` = false).

> **Catatan Validasi & UI:**
> Input `Deskripsi`, `URL Guide Book`, dan `Contact Person 1` tidak diwajibkan (optional) saat superadmin membuat atau mengedit event. Namun, form tersebut bersifat wajib (mandatory) ketika Panitia Lomba memperbarui detail event melalui modul Edit Panitia Lomba.

### `Team`

Tabel: `team`

Relasi:

- `event()`
- `paymentProof()`
- `members()`
- `users()`
- `submissions()`

Field penting:

- `is_document_verified`: status verifikasi dokumen/berkas tim.
- `is_verified`: status pembayaran/transaksi.
- `verification_error`: alasan penolakan.
- `payment_proof_id`: media bukti bayar.

### `TeamMember`

Tabel: `team_member`

Relasi:

- `user()`
- `team()`
- `kartu()`

Field penting:

- `role`
- `is_verified`: Status verifikasi dokumen anggota secara individual.
- `verification_error`
- `kartu_id`

### `Media`

Tabel: `media`

Dipakai untuk:

- Bukti pembayaran.
- Kartu identitas/KTM.
- Dokumen submission.
- Twibbon atau file audit lain.

## Staff Management

Halaman: `/admin/staff`

Fitur:

- Superadmin dapat membuat staff baru tanpa mengatur password. Sistem secara otomatis mengirimkan email berisi link pembuatan password.
- Akun staff baru (admin/panitia_lomba) secara default tidak aktif (`is_verified = false`). Akun otomatis menjadi aktif setelah staff mengatur password mereka sendiri via email.
- Superadmin dapat mengedit detail staff.
- Superadmin dapat menghapus staff, kecuali akun sendiri.
- Minimal harus ada satu superadmin.
- Edit staff melakukan fetch detail terbaru melalui `GET /admin/staff/{staff}` sebelum modal dibuka.
- Field "Kompetisi yang dikelola" hanya dirender untuk role `panitia_lomba`.
- Role `admin_biasa` dan `superadmin` tidak menyimpan assignment event.

## Pengumuman

Halaman: `/admin/announcements`

Fitur:

- Superadmin, admin biasa, dan panitia_lomba dapat membuka halaman pengumuman.
- Superadmin dan admin biasa dapat mengelola pengumuman untuk semua event, serta pengumuman "Umum" (Seluruh Peserta).
- Panitia Lomba hanya dapat melihat dan mengelola pengumuman pada event yang ditugaskan (tidak bisa membuat pengumuman Umum).
- Sistem mencatat waktu akses pengumuman peserta ke kolom `user.last_read_announcements_at` via endpoint khusus di web API, sehingga *frontend* dashboard peserta (React) dapat menampilkan titik merah indikator pengumuman baru yang usianya lebih segar dibanding waktu baca terakhir.

## Timeline Kompetisi

Halaman:

- `/admin/timelines`
- `/admin/timelines/{event}/agenda`

Fitur:

- Superadmin dapat mengelola seluruh event dan kompetisi.
- Admin Biasa dapat mengelola khusus event `non_competition`.
- Superadmin, Admin Biasa, dan Panitia Lomba dapat mengelola agenda timeline kegiatan sesuai wewenang.
- Panitia Lomba hanya dapat mengelola agenda pada kompetisi atau event yang ditugaskan kepadanya.
- **competition_timeline (Agenda Global Kompetisi)**: Menyimpan timeline yang berlaku secara umum untuk semua kompetisi (tidak memiliki relasi `event_id`). Halaman ini hanya dapat dibuat, diedit, atau dihapus oleh Superadmin. Panitia Lomba hanya dapat melihatnya sebagai info read-only.

## Data Dan Berkas Tim

Halaman:

- `/operation/teams`
- `/operation/teams/{id}`

Fitur:

- Superadmin dan panitia_lomba dapat melihat daftar tim dan detail berkas.
- Panitia Lomba hanya melihat tim dari event yang ditugaskan.
- Penolakan verifikasi berkas tim membutuhkan alasan.
- Jika alasan penolakan kosong, UI menampilkan pesan inline tanpa native JavaScript alert.
- Tim tidak bisa disetujui selama masih ada catatan kesalahan pada dokumen anggota.
- Setelah peserta memperbarui berkas, panitia_lomba menekan `Setuju` pada anggota yang sudah valid untuk mengosongkan catatan kesalahan, lalu menyetujui ulang berkas tim.
- Tombol `Tolak` pada anggota wajib menyertakan catatan kesalahan.
- Kartu anggota menampilkan ketua di urutan paling atas dan menyediakan dropdown data lengkap peserta.

## Transaksi & Verifikasi Event

Halaman:

- `/admin/transactions` (Tim Kompetisi)
- `/admin/event-participants` (Peserta Non-Kompetisi)

Endpoint JSON & Verifikasi:

- `POST /transaction/{teamId}/verify` (Kompetisi)
- `POST /admin/event-participants/verify` (Non-Kompetisi)
- `GET /transaction/recap`

Fitur:

- Superadmin dan admin biasa dapat memverifikasi pembayaran.
- Tim baru masuk ke antrean transaksi dengan status `pending` setelah seluruh
  berkas tim disetujui. Penolakan berkas tidak boleh mengubah status transaksi
  menjadi `rejected`.
- Terdapat filter berdasarkan event dan status (Semua Status, Pending, Accepted, Rejected).
- Status default pada view adalah Pending & Rejected.
- Reject pembayaran membutuhkan alasan penolakan.
- Verifikasi yang sudah disetujui (Accepted) akan **dikunci** (locked) dan tidak bisa diubah kembali. Tombol aksi disembunyikan dan backend memblokir request.
- Rekap transaksi menghitung akumulasi dana dari tim yang sudah diterima.

## Export CSV

Route:

- `GET /export/teams?event_id=...`
- `GET /export/participants?event_id=...`
- `GET /export/teams/global`
- `GET /export/participants/global`

Implementasi:

- `App\Exports\TeamRecapExport`
- `App\Exports\ParticipantRecapExport`
- `ExportController`

Detail:

- Response menggunakan streamed CSV.
- CSV diberi UTF-8 BOM agar lebih aman dibuka di spreadsheet.
- Header `Content-Disposition` memaksa download file `.csv`.

## Seeder

Seeder utama: `database/seeders/DatabaseSeeder.php`.

Data yang dibuat:

- Event kompetisi: Hack Today, UX Today, Code Today, Mine Today.
- Event non-kompetisi: Seminar Nasional IT Today.
- Akun superadmin, admin biasa, panitia_lomba, dan peserta.
- Tim dan team member.
- Media PDF dummy.
- Media image contoh untuk bukti pembayaran.
- Media image contoh untuk KTM/kartu identitas.
- Timeline event.
- Event participant.
- Pengumuman awal.

## UI

Komponen utama:

- Blade layout `resources/views/layouts/app.blade.php`
- Navigation `resources/views/layouts/navigation.blade.php`
- Admin layout `resources/views/components/admin/layout.blade.php`

Interaksi frontend:

- Alpine.js untuk modal, search table, fetch detail staff, dan validasi inline.
- Tailwind CSS untuk styling.
- Tombol `Periksa Berkas` pada tabel tim menggunakan badge biru agar mudah
  dibedakan dari badge status verifikasi.
- Pendaftaran kompetisi individu ditampilkan sebagai peserta pada daftar dan
  detail verifikasi berkas maupun transaksi; nama tim serta kode gabung
  internal tidak ditampilkan. ID pendaftaran versi pendek ditampilkan untuk
  membedakan peserta yang memiliki nama sama.

## Validasi Dan Guard

Guard penting:

- `auth`
- `verified`
- `data_frozen`

Validasi role saat ini dilakukan terutama di controller:

- `ensureSuperadmin()`
- `isAdminStaff()`
- `ensureCompetitionTimelineManager()`
- pengecekan role inline dengan `abort_unless(...)`
- pembatasan panitia_lomba melalui `event_staff`

## Catatan Teknis

- `bootstrap/app.php` baru mendaftarkan middleware `data_frozen`. Role guard belum dibuat sebagai middleware reusable, sehingga pembatasan role masih tersebar di controller.
- Pengumuman dapat ditujukan ke satu event melalui `event_id` atau ke seluruh peserta dengan `event_id = NULL`. Migrasi koreksi `2026_07_06_090000_repair_nullable_event_id_on_event_announcement_table.php` memastikan database lama benar-benar menerima pengumuman umum meskipun migrasi nullable sebelumnya pernah tercatat tanpa mengubah kolom fisik.
- Menu Timeline menyediakan `Kelola Agenda` untuk setiap record pada tabel `event`, baik kompetisi maupun non-kompetisi. Superadmin dapat mengelola seluruh agenda, `panitia_lomba` dibatasi ke kompetisi yang ditugaskan melalui `event_staff`, dan `admin_biasa` dibatasi ke event non-kompetisi. Agenda disimpan di `event_timeline` dan langsung dikonsumsi API untuk panel agenda pada halaman detail web peserta.
- Label admin untuk tautan komunitas peserta memakai istilah “WA/Discord” di tampilan superadmin, admin biasa, dan panitia lomba. Field teknis tetap `whatsapp_group_link` agar kompatibel dengan skema lama.
- **Penyimpanan Berkas (Storage):** Secara default logo event dan berkas-berkas penting disimpan ke S3/Cloudflare R2 jika diatur (saat production). Namun untuk menghindari error 500 saat mode lokal, sistem mendeteksi `config('filesystems.default') === 'local'` dan akan fallback secara otomatis untuk menggunakan disk `public`.
- Pada setup lokal, `php artisan storage:link` wajib dijalankan agar `logo_url` yang dikirim API (`/storage/events/logos/...`) dapat diakses landing page, halaman detail, dan dashboard. Tanpa symlink `public/storage`, Laravel membalas 403 meskipun file upload tersimpan dengan benar.
- PHPUnit di environment ini belum bisa dijalankan penuh karena driver SQLite tidak tersedia.
- Route cache dan Blade cache perlu dibersihkan setelah perubahan UI/route: `php artisan route:clear`, `php artisan view:clear`.
