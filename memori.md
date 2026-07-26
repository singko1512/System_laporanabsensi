# Memori

## Catatan Proyek

- Project Laravel absensi disiapkan untuk hosting PHP 8.2.
- Runtime lokal Laragon saat dicek masih PHP 8.3.32, tapi dependency Composer sudah dikunci agar kompatibel PHP 8.2.
- File perubahan dari `C:\Users\arjun\Downloads\perubahan-untuk-hosting` sudah dipindahkan ke struktur Laravel project.
- Konfigurasi database lokal menggunakan MySQL Laragon:
  - `DB_CONNECTION=mysql`
  - `DB_DATABASE=system_laporanabsensi`
  - `DB_USERNAME=root`
  - `DB_PASSWORD=` kosong
- Database `system_laporanabsensi` ditemukan di data Laragon MySQL 8.4, tapi saat dicek service MySQL belum aktif/port 3306 refused.
- Admin PIN dari perubahan hosting sekarang `180909`.
- `npm install --ignore-scripts` dan `npm run build` sudah berhasil.
- Validasi Laravel yang sudah berhasil: `composer validate`, `composer install`, `php artisan about`, `php artisan route:list`, PHP lint, `php artisan view:cache`.
- `php artisan test` belum bisa jalan karena sebelumnya PHP tidak punya `pdo_sqlite`; setelah disesuaikan ke MySQL, test butuh database `system_laporanabsensi_testing`.
- Dummy data pegawai sudah dibuat melalui `DatabaseSeeder`: 8 pegawai, 8 project timeline, 24 note timeline, dan 40 absensi dummy. Seeder sudah berhasil dijalankan ke database aktif.
- Data user sekarang disesuaikan ke konteks magang:
  - Tabel `md_user` punya kolom `pembimbing_magang` dan `bidang_magang`.
  - Admin bisa tambah/edit pembimbing magang dan bidang magang dari Kelola Magang.
  - Tabel daftar magang, export Excel, dan export PDF menampilkan pembimbing/bidang magang.
  - Daftar Peserta Magang di admin punya pencarian khusus untuk nama/email/bidang/pembimbing dan pengelompokan berdasarkan nama pembimbing magang.
  - Dummy 8 peserta magang sudah diisi pembimbing dan bidang.
  - Label UI yang sebelumnya terlihat sebagai pegawai/karyawan sudah diganti menjadi peserta magang di bagian utama aplikasi.
  - Validasi terakhir untuk perubahan ini berhasil: `php artisan migrate --no-interaction`, `php artisan db:seed --no-interaction`, `php artisan view:cache`, `php artisan route:list`, dan `php artisan test --filter=AttendanceTest` dengan 5 test passed.
- Ide fitur sertifikat:
  - Sertifikat bisa dibuat otomatis saat `tanggal_selesai_magang` sudah lewat/sama dengan hari ini.
  - Pengiriman email bisa memakai SMTP email domain dari hosting, misalnya `magang@domain.com`.
  - Spek hosting user cukup secara umum karena ada unlimited email account, SSH/Terminal, Composer, Git, NodeJS, SSL, dan storage 10 GB NVMe.
  - Tetap perlu cek detail hosting: PHP 8.2, MySQL, cron job, document root ke `public`, data SMTP, dan limit kirim email per jam/hari.
  - Rekomendasi implementasi awal: tombol admin `Kirim Sertifikat`, generate PDF, kirim email via SMTP hosting, lalu simpan `sertifikat_dikirim_pada` agar tidak terkirim berulang.
  - Full otomatis harian bisa dibuat setelah cron job hosting dipastikan tersedia, menggunakan `php artisan schedule:run`.
- Enum database sudah diganti ke master data:
  - Tabel baru `md_master_data` berisi `jenis`, `kode`, `nama`, `warna`, `urutan`, dan `is_active`.
  - Master data default: `absensi_status` (hadir/wfh/sakit/izin), `jadwal_status` (wfo/wfh), `project_status` (aktif/selesai), `note_kategori` (rendah/sedang/tinggi).
  - Kolom enum lama dikonversi menjadi foreign key: `md_absensi.status_id`, `md_jadwal_mingguan.*_status_id`, `md_projects.status_id`, `md_project_notes.kategori_id`.
  - File migration lama juga sudah disesuaikan agar fresh install ke depan tidak membuat `enum()`.
  - Model tetap menyediakan accessor lama seperti `$absensi->status`, `$project->status`, `$note->kategori`, dan `$jadwal->senin` agar view/controller lama tetap kompatibel.
  - UI pilihan status/kategori/jadwal mulai dibaca dari master data, jadi nama label bisa diganti dari tabel master tanpa ubah schema.
  - Validasi terakhir berhasil: tidak ada `enum()` tersisa di `database/migrations`, `php artisan migrate --no-interaction`, `php artisan db:seed --no-interaction`, `php artisan view:cache`, `php artisan route:list`, dan `php artisan test --filter=AttendanceTest` dengan 5 test passed.
- Timeline project admin sudah ditambah multi-pegawai:
  - Saat membuat/edit project, pegawai dipilih dengan checkbox dan bisa pilih lebih dari satu.
  - UI pilih pegawai di form timeline admin sudah dibuat ringkas: klik field `Pilih anggota project` untuk membuka popup checklist anggota.
  - Project punya daftar member (`md_project_user`).
  - Admin bisa drag nama pegawai dari header project ke kotak hari untuk menyimpan assignment harian (`md_project_day_assignments`).
  - Setelah admin drag/drop pegawai ke kotak hari, modal tambah note langsung terbuka dengan target pegawai tersebut.
  - Note timeline sekarang bisa punya target pegawai (`md_project_notes.user_id`), sehingga chip note di hari menampilkan note itu untuk siapa.
  - Tampilan kotak hari sudah dirapikan: kalau pegawai yang di-drop sudah punya note aktif di hari itu, chip nama assignment disembunyikan supaya nama tidak tampil dobel. Yang tampil cukup card note dengan nama pegawai target.
  - Assignment harian bisa dihapus dari kotak hari dengan tombol X.
  - Halaman timeline user juga menampilkan project kalau pegawai tersebut menjadi member project.
- Progress terakhir sudah disimpan: form timeline admin memakai popup daftar anggota, bukan checkbox besar di halaman utama. Validasi terakhir berhasil dengan `php artisan route:list` dan `php artisan view:cache`.

## Keputusan

- Laravel 13 diturunkan ke Laravel 12 karena Laravel 13 butuh PHP minimal 8.3, sedangkan hosting target PHP 8.2.
- `composer.json` memakai `php: ^8.2`, `laravel/framework: ^12.0`, dan `config.platform.php: 8.2.0`.
- `laravel/pao` dihapus karena tidak cocok dengan target PHP 8.2.
- `.env.example` dibuat ulang untuk MySQL Laragon.
- `phpunit.xml` diarahkan ke database MySQL test terpisah `system_laporanabsensi_testing` agar test tidak mereset database utama.
- File `.gitignore` dari folder perubahan hosting tidak ditaruh di root karena isinya meng-ignore semua file; ditempatkan di `public/uploads/.gitignore`.
- Ditambahkan model dan migration `JadwalMingguan` karena controller/view baru membutuhkan fitur jadwal mingguan.

## TODO

- Jalankan Laragon dan start MySQL.
- Jalankan `php artisan migrate` untuk memasang migration baru ke database utama.
- Untuk isi ulang dummy data, jalankan `php artisan db:seed --no-interaction`.
- Server lokal terakhir distart di `http://127.0.0.1:8000`.
- Kalau ingin menjalankan test, buat database `system_laporanabsensi_testing` dulu lalu jalankan `php artisan test`.
- Jalankan aplikasi lokal dengan `php artisan serve`, lalu buka `http://127.0.0.1:8000`.
- Saat hosting nanti, pastikan PHP hosting 8.2.x, extension Laravel aktif, dan document root mengarah ke folder `public`.
