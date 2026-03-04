<p align="center">
    <h1 align="center">💰 Personal Finance ERP & Smart Family Wealth</h1>
</p>

## 🚀 Tentang Proyek
Aplikasi **Personal Finance ERP** adalah sistem manajemen keuangan keluarga kelas enterprise (menggunakan konsep Single-Ledger/Double-Entry Accounting) yang dirancang khusus untuk mempermudah pencatatan, pemantauan, dan perencanaan keuangan rumah tangga maupun invidu. 

Berbeda dengan aplikasi pencatat pengeluaran biasa, sistem ini dibangun di atas pondasi akuntansi yang solid (Chart of Accounts, Journal Entries) namun disajikan dalam antarmuka (UI) yang sangat mudah digunakan (*user-friendly*) berkat fitur **Smart Transaction**.

Sistem ini mendukung pengelolaan banyak profil keuangan melalui fitur **Multitenant Workspaces**, memungkinkan Anda memisahkan keuangan Pribadi, Keluarga, maupun Bisnis Kecil dalam satu aplikasi.

---

## 🌟 Fitur Utama (Core Features)

### 1. 📊 Smart Dashboard & Analytics
Dashboard komprehensif yang menampilkan kondisi finansial secara *real-time*:
- **Total Net Worth** (Kekayaan Bersih = Total Aset - Total Hutang).
- **Cashflow Bulanan** (Pemasukan vs Pengeluaran).
- **Status Dana Darurat** (Kalkulasi kesiapan dana darurat berdasarkan rata-rata pengeluaran vs target bulan).
- **Monitoring Kartu Kredit & Paylater** (Pemantauan limit vs usage/penggunaan).
- **Ringkasan Cicilan Aktif** (Total kewajiban bulanan dan sisa hutang keseluruhan).
- **Pengingat Tagihan (Upcoming Bills)** (Peringatan tagihan yang akan jatuh tempo dalam 7 hari ke depan).
- **Budget Monitoring** (Pemantauan realisasi anggaran bulanan dengan progress bar).

### 2. ⚡ Smart Transaction Form
Satu form pintar untuk semua jenis transaksi keuangan:
- **Pengeluaran**: Mencatat biaya belanja bulanan, tagihan, dll.
- **Pemasukan**: Mencatat gaji, bonus, dividen, dll.
- **Transfer**: Memindahkan dana antar kas/bank.
- **Investasi**: Membeli aset investasi (Emas, Saham, dll), otomatis mengurangi saldo bank dan menambah stok di Portofolio Investasi.
- Seluruh input angka sudah menggunakan format ribuan otomatis (AutoNumeric) untuk mencegah salah ketik.

### 3. 🏦 Chart of Accounts (COA) Sistem Ganda
Sistem menggunakan *Double-Entry Accounting* di balik layar:
- Mendukung kategori Asset, Liability, Equity, Income, dan Expense.
- Sub-kategori spesifik (Cash, Bank, Investment, Fixed Asset).

### 4. 📈 Investment Portfolio Management
- Pelacakan aset (Emas, Saham, Crypto, Reksadana, Properti).
- Perhitungan **Unrealized Gain/Loss** (Keuntungan/Kerugian belum terealisasi).
- Kalkulasi Modal (Cost Basis) vs Nilai Pasar (Market Value).

### 5. 💳 Hutang, Cicilan & Manajemen Limit
- Pelacakan cicilan KPR, Kendaraan, atau Gadget dengan progress pembayaran (Bulan dan Persentase).
- Menghitung sisa tenor, bunga, dan angsuran bulanan.
- Pelacakan batas penggunaan (Credit Limit) untuk Kartu Kredit dan layanan Paylater.

### 6. 📅 Recurring Transactions (Tagihan Rutin)
- Penjadwalan otomatis untuk tagihan berulang (Listrik, Internet, Asuransi).
- Sinkronisasi otomatis dengan notifikasi Dashboard untuk mengingatkan jatuh tempo.

### 7. 🎯 Smart Budgeting
- Perencanaan anggaran berbasis zero-based / envelope system.
- Peringatan dini (*early warning*) di Dashboard apabila pengeluaran mendekati atau melebihi limit.

---

## 🛠️ Stack Teknologi
- **Backend**: Laravel 11.x (PHP 8.2+)
- **Frontend / UI**: AdminLTE 3 (Bootstrap 4)
- **Database**: MySQL / MariaDB
- **JavaScript Libraries**: 
  - jQuery
  - Select2 (untuk dropdown interaktif)
  - AutoNumeric.js (untuk format pemisah ribuan otomatis *real-time*)
  - SweetAlert2 / Toastr (untuk fitur notifikasi)

---

## 📥 Panduan Instalasi (Setup Guide)

1. **Clone repositori ini:**
   ```bash
   git clone <repo-url>
   cd personal-finance-erp
   ```

2. **Install Dependencies:**
   ```bash
   composer install
   npm install
   npm run build
   ```

3. **Konfigurasi Environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Atur koneksi database di file `.env` (DB_DATABASE, DB_USERNAME, DB_PASSWORD).

4. **Jalankan Migrasi & Seeder Database:**
   ```bash
   php artisan migrate:fresh
   php artisan db:seed --class=ChartOfAccountsSeeder
   php artisan db:seed --class=SampleDataSeeder
   ```
   *(Catatan: `SampleDataSeeder` akan otomatis membuat 1 user default, 1 workspace, simulasi transaksi 3 bulan terakhir, aset investasi, cicilan, dan tagihan rutin untuk keperluan demonstrasi).*

5. **Jalankan Server Lokal:**
   ```bash
   php artisan serve
   ```
   Akses aplikasi di: `http://localhost:8000`

---

## 👨‍💻 Evaluasi Alur Kerja (Workflow Evaluation)
Sistem ini telah melalui fase *end-to-end testing* dengan hasil:
- **Routing & Controllers**: Semua namespace telah di-*bind* dengan benar, tidak ada error `Target class does not exist`.
- **UI Data Binding**: Data dari *Service Layer* (`FinancialSummaryService`) ter-mapping secara akurat ke komponen Blade Dashboard.
- **Double-Entry Validation**: Nilai Debit dan Kredit balance divalidasi dengan ketat pada form Jurnal Manual dan Smart Transaction.
- **Blade Syntax Integrity**: Tidak ada konflik atau syntax error pada direktif `@foreach`, `@forelse`, dan `@if`.
- **UX Form**: Masalah duplikasi input angka telah diatasi dengan inisialisasi tunggal melalui `app-custom.js`.

---

<p align="center">
  Dibuat dengan ❤️ untuk mencapai Kebebasan Finansial.
</p>
