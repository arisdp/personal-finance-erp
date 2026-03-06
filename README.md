<p align="center">
    <h1 align="center">💰 Personal Finance ERP & Smart Family Wealth</h1>
</p>

## 🚀 Tentang Proyek
Aplikasi **Personal Finance ERP** adalah sistem manajemen keuangan keluarga kelas enterprise yang menggunakan konsep **Double-Entry Accounting** (Akuntansi Berpasangan). Dirancang khusus untuk mempermudah pencatatan, pemantauan, dan perencanaan keuangan rumah tangga, freelancer, maupun bisnis kecil dengan akurasi setara software akuntansi profesional namun dengan kemudahan penggunaan aplikasi personal.

Sistem ini mendukung pengelolaan banyak profil keuangan melalui fitur **Multi-Workspace System**, memungkinkan isolasi data yang ketat antara keuangan Pribadi, Keluarga, dan Bisnis dalam satu akun.

---

## 🌟 Fitur Unggulan (Premium Features)

### 1. 📊 Dashboard Finansial Eksekutif
Dasbor interaktif yang merangkum kesehatan finansial Anda dalam sekejap:
- **Net Worth Tracker**: Pantau kekayaan bersih (Aset - Hutang) secara real-time.
- **Investment Portfolio Summary**: Ringkasan nilai pasar, modal, dan profit/loss investasi.
- **Emergency Fund Ratio**: Kalkulator cerdas untuk mengukur kesiapan dana darurat.
- **Credit Limit Monitor**: Visualisasi penggunaan limit Kartu Kredit & Paylater menggunakan progress bar.
- **Upcoming Bills**: Pengingat otomatis untuk tagihan rutin yang akan jatuh tempo.

### 2. ⚡ Smart Transaction (Otomasi 1-Layar)
Antarmuka pencatatan cerdas yang menyederhanakan prinsip akuntansi Debit/Kredit:
- **Pencatatan Cepat**: Dukungan untuk Pengeluaran, Pemasukan, Transfer, Investasi, dan Penyetoran Modal.
- **Master Instrument Linkage**: Pilih aset investasi dari Master Harga; Nama, Tipe, dan Ticker otomatis terisi.
- **Pemisah Ribuan Otomatis**: Integrasi AutoNumeric untuk mencegah kesalahan input nominal.
- **Smart Debt Payment**: Mencatat pembayaran cicilan sekaligus memperbarui sisa tenor dan hutang secara otomatis.

### 3. 📈 Manajemen Investasi Terpusat (Master Harga)
Sistem pengelolaan aset investasi yang canggih:
- **Master Instrumen**: Update harga instrumen (misal: Emas, BBCA, BTC) di satu tempat; seluruh portofolio yang terkait otomatis terupdate.
- **Unrealized Gain/Loss**: Pantau performa ROI (Return on Investment) per aset atau keseluruhan portofolio.
- **Historical Prices**: Otomasi pencatatan riwayat harga setiap kali transaksi terjadi.

### 4. � Akuntansi & Pelaporan Profesional
Laporan keuangan standar akuntansi yang mudah dipahami:
- **Hierarchical COA (Tree View)**: Struktur akun bertingkat dengan perhitungan saldo rekursif (Induk menjumlahkan seluruh Anak).
- **Neraca Saldo (Trial Balance)**: Laporan posisi aset, hutang, dan modal yang terstruktur.
- **Laba Rugi (Profit & Loss)**: Analisis pendapatan vs beban per periode.
- **Buku Besar (General Ledger)**: Detail mutasi per akun dengan perhitungan saldo awal otomatis.
- **Ekspor PDF**: Semua laporan dapat dicetak atau disimpan dalam format PDF secara rapi.

### 5. 📅 Perencanaan & Kontrol (Planning)
- **Smart Budgeting**: Set batas pengeluaran per kategori akun dan terima peringatan jika melebihi anggaran.
- **Recurring Bills**: Kelola tagihan rutin (Internet, Listrik, Asuransi) agar tidak ada yang terlewat.
- **Installment Tracker**: Lacak sisa tenor dan total sisa hutang untuk cicilan jangka panjang (KPR/Kendaraan).

---

## 🛠️ Stack Teknologi
- **Core Framework**: Laravel 11.x (PHP 8.2+)
- **Frontend Template**: AdminLTE 3 (Bootstrap 4)
- **Database**: PostgreSQL / MySQL (Universal Support)
- **Library Pendukung**: 
  - **AutoNumeric.js**: Validasi input mata uang real-time.
  - **DomPDF**: Generator laporan PDF berkualitas tinggi.
  - **Select2**: Dropdown interaktif dan pencarian akun.
  - **Chart.js**: Visualisasi data dan proyeksi masa depan.

---

## 📥 Panduan Instalasi (Setup Guide)

1. **Clone repositori:**
   ```bash
   git clone <repo-url>
   cd personal-finance-erp
   ```

2. **Install Dependencies:**
   ```bash
   composer install
   npm install && npm run build
   ```

3. **Konfigurasi Environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Sesuaikan pengaturan DB_ di file `.env`.*

4. **Persiapan Database:**
   ```bash
   php artisan migrate --seed
   ```
   *(Menambahkan Chart of Accounts standar dan data sampel untuk demonstrasi).*

5. **Jalankan Aplikasi:**
   ```bash
   php artisan serve
   ```
   Akses di: `http://localhost:8000`

---

## �️ Keamanan & Integritas Data
- **Audit Trail**: Mencatat siapa yang membuat, mengubah, atau menghapus setiap entri jurnal secara otomatis.
- **Soft Deletes**: Data yang dihapus tidak benar-benar hilang dari database, memudahkan penelusuran jika terjadi kesalahan.
- **Data Isolation**: Multi-Workspace memastikan data antar profil keuangan tidak akan pernah bercampur.

---

<p align="center">
  <i>Dibuat dengan ❤️ untuk membantu Anda mencapai kebebasan finansial melalui manajemen data yang akurat.</i>
</p>
