<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        // Truncate handles relationships if configured, but let's just delete to be safe
        DB::table('journal_lines')->delete();
        DB::table('journal_entries')->delete();
        DB::table('accounts')->delete();

        $now = Carbon::now();

        // Helper function
        $insert = function ($code, $name, $type, $category, $parentId = null, $isPostable = true, $creditLimit = null, $trackLimit = false) use ($now) {
            $id = Str::uuid()->toString();

            DB::table('accounts')->insert([
                'id' => $id,
                'code' => $code,
                'name' => $name,
                'type' => $type,
                'category' => $category,
                'parent_id' => $parentId,
                'is_postable' => $isPostable,
                'credit_limit' => $creditLimit,
                'track_limit' => $trackLimit,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return $id;
        };

        /*
        |--------------------------------------------------------------------------
        | ASSETS (1000)
        |--------------------------------------------------------------------------
        */
        $assets = $insert('1000', 'AKTIVA (ASSETS)', 'asset', 'asset', null, false);

        // Kas dan Bank
        $cashBank = $insert('1100', 'KAS DAN BANK', 'asset', 'asset', $assets, false);
        
        $kasTunaiGroup = $insert('1110', 'KAS TUNAI', 'asset', 'asset', $cashBank, false);
        $insert('1111', 'Kas Tunai (Dompet)', 'cash', 'asset', $kasTunaiGroup);
        
        $bankUmumGroup = $insert('1120', 'BANK UMUM / KONVENSIONAL', 'asset', 'asset', $cashBank, false);
        $insert('1121', 'Bank Mandiri (Utama)', 'bank', 'asset', $bankUmumGroup);
        $insert('1122', 'Bank BCA', 'bank', 'asset', $bankUmumGroup);
        $insert('1123', 'Bank BNI', 'bank', 'asset', $bankUmumGroup);
        $insert('1124', 'Bank BRI', 'bank', 'asset', $bankUmumGroup);
        $insert('1125', 'Bank CIMB Niaga', 'bank', 'asset', $bankUmumGroup);

        $bankDigitalGroup = $insert('1130', 'BANK DIGITAL', 'asset', 'asset', $cashBank, false);
        $insert('1131', 'SeaBank', 'bank', 'asset', $bankDigitalGroup);
        $insert('1132', 'Bank Jago', 'bank', 'asset', $bankDigitalGroup);
        $insert('1133', 'Blu by BCA Digital', 'bank', 'asset', $bankDigitalGroup);
        $insert('1134', 'Bank Aladin Digital', 'bank', 'asset', $bankDigitalGroup);
        $insert('1135', 'Bank Neo Commerce', 'bank', 'asset', $bankDigitalGroup);
        
        $ewalletGroup = $insert('1140', 'E-WALLET', 'asset', 'asset', $cashBank, false);
        $insert('1141', 'Gopay', 'ewallet', 'asset', $ewalletGroup);
        $insert('1142', 'OVO', 'ewallet', 'asset', $ewalletGroup);
        $insert('1143', 'ShopeePay', 'ewallet', 'asset', $ewalletGroup);
        $insert('1144', 'Dana', 'ewallet', 'asset', $ewalletGroup);
        $insert('1145', 'LinkAja', 'ewallet', 'asset', $ewalletGroup);

        // Piutang
        $receivables = $insert('1200', 'PIUTANG', 'asset', 'asset', $assets, false);
        $insert('1210', 'Piutang Teman/Keluarga', 'asset', 'asset', $receivables);

        // Investasi
        $investments = $insert('1300', 'INVESTASI', 'asset', 'asset', $assets, false);
        
        $emas = $insert('1310', 'INVESTASI EMAS', 'asset', 'asset', $investments, false);
        $insert('1311', 'Emas Fisik (Antam)', 'investment', 'asset', $emas);
        $insert('1312', 'Emas Digital (Pegadaian)', 'investment', 'asset', $emas);

        $pasarModal = $insert('1320', 'INVESTASI PASAR MODAL', 'asset', 'asset', $investments, false);
        $insert('1321', 'Saham (Stock)', 'investment', 'asset', $pasarModal);
        $insert('1322', 'Reksadana', 'investment', 'asset', $pasarModal);
        $insert('1323', 'Obligasi / SBN', 'investment', 'asset', $pasarModal);
        $insert('1324', 'Rekening Dana Nasabah (RDN)', 'bank', 'asset', $pasarModal);

        $crypto = $insert('1330', 'INVESTASI CRYPTO', 'asset', 'asset', $investments, false);
        $insert('1331', 'Bitcoin (BTC)', 'investment', 'asset', $crypto);
        $insert('1332', 'Ethereum (ETH)', 'investment', 'asset', $crypto);
        $insert('1333', 'Stablecoin (USDT/USDC)', 'investment', 'asset', $crypto);

        $p2pLending = $insert('1340', 'INVESTASI P2P LENDING', 'asset', 'asset', $investments, false);
        $insert('1341', 'Amartha', 'investment', 'asset', $p2pLending);
        $insert('1342', 'Modalku', 'investment', 'asset', $p2pLending);
        $insert('1343', 'Asetku', 'investment', 'asset', $p2pLending);

        // Aset Tetap
        $fixedAssets = $insert('1400', 'ASET TETAP', 'fixed_asset', 'asset', $assets, false);
        $insert('1411', 'Tanah', 'fixed_asset', 'asset', $fixedAssets);
        $insert('1412', 'Bangunan (Rumah/Apartemen)', 'fixed_asset', 'asset', $fixedAssets);
        $insert('1421', 'Mobil', 'fixed_asset', 'asset', $fixedAssets);
        $insert('1422', 'Sepeda Motor', 'fixed_asset', 'asset', $fixedAssets);
        $insert('1430', 'Peralatan, Gadget & Elektronik', 'fixed_asset', 'asset', $fixedAssets);

        // Dana Darurat
        $emergencyFunds = $insert('1500', 'DANA DARURAT', 'asset', 'asset', $assets, false);
        $insert('1510', 'Dana Darurat (Tabungan Bank)', 'bank', 'asset', $emergencyFunds);
        $insert('1520', 'Dana Darurat (Logam Mulia)', 'investment', 'asset', $emergencyFunds);

        /*
        |--------------------------------------------------------------------------
        | LIABILITIES (2000)
        |--------------------------------------------------------------------------
        */
        $liabilities = $insert('2000', 'KEWAJIBAN (LIABILITIES)', 'liability', 'liability', null, false);

        // Hutang Jangka Pendek
        $currentLiab = $insert('2100', 'HUTANG JANGKA PENDEK', 'liability', 'liability', $liabilities, false);
        
        $creditCards = $insert('2110', 'KARTU KREDIT', 'liability', 'liability', $currentLiab, false);
        $insert('2111', 'Kartu Kredit Mandiri', 'liability', 'liability', $creditCards, true, 20000000, true);
        $insert('2112', 'Kartu Kredit BCA', 'liability', 'liability', $creditCards, true, 10000000, true);

        $paylaters = $insert('2120', 'PAYLATER & PINJOL', 'liability', 'liability', $currentLiab, false);
        $insert('2121', 'Shopee Paylater', 'liability', 'liability', $paylaters, true, 5000000, true);
        $insert('2122', 'Traveloka Paylater', 'liability', 'liability', $paylaters, true, 5000000, true);
        $insert('2123', 'Kredivo', 'liability', 'liability', $paylaters, true, 10000000, true);
        $insert('2124', 'Akulaku', 'liability', 'liability', $paylaters, true, 5000000, true);
        $insert('2125', 'GoPay Later', 'liability', 'liability', $paylaters, true, 2000000, true);

        // Hutang Jangka Panjang
        $longTermLiab = $insert('2200', 'HUTANG JANGKA PANJANG', 'liability', 'liability', $liabilities, false);
        $insert('2210', 'KPR Rumah', 'liability', 'liability', $longTermLiab);
        $insert('2221', 'Cicilan Mobil', 'liability', 'liability', $longTermLiab);
        $insert('2222', 'Cicilan Motor', 'liability', 'liability', $longTermLiab);
        $insert('2230', 'Pinjaman Bank (Multiguna)', 'liability', 'liability', $longTermLiab);

        /*
        |--------------------------------------------------------------------------
        | EQUITY (3000)
        |--------------------------------------------------------------------------
        */
        $equity = $insert('3000', 'EKUITAS (EQUITY)', 'equity', 'equity', null, false);
        $insert('3100', 'Modal / Kekayaan Awal', 'equity', 'equity', $equity);
        $insert('3200', 'Laba Ditahan (Saldo Laba)', 'equity', 'equity', $equity);

        /*
        |--------------------------------------------------------------------------
        | INCOME (4000)
        |--------------------------------------------------------------------------
        */
        $income = $insert('4000', 'PENDAPATAN (INCOME)', 'income', 'income', null, false);
        
        $activeIncome = $insert('4100', 'PENDAPATAN AKTIF', 'income', 'income', $income, false);
        $insert('4110', 'Gaji Pokok', 'income', 'income', $activeIncome);
        $insert('4120', 'Bonus & THR', 'income', 'income', $activeIncome);
        $insert('4130', 'Pendapatan Sampingan', 'income', 'income', $activeIncome);

        $passiveIncome = $insert('4200', 'PENDAPATAN PASIF', 'income', 'income', $income, false);
        $insert('4210', 'Dividen Saham', 'income', 'income', $passiveIncome);
        $insert('4220', 'Kupon Obligasi', 'income', 'income', $passiveIncome);
        $insert('4230', 'Hasil Sewa', 'income', 'income', $passiveIncome);

        /*
        |--------------------------------------------------------------------------
        | EXPENSE (5000)
        |--------------------------------------------------------------------------
        */
        $expense = $insert('5000', 'BEBAN (EXPENSES)', 'expense', 'expense', null, false);

        // Pengeluaran Rutin
        $fixedExpense = $insert('5100', 'PENGELUARAN RUTIN (FIXED)', 'expense', 'expense', $expense, false);
        $insert('5110', 'Zakat, Infaq & Sedekah', 'expense', 'expense', $fixedExpense);
        $insert('5120', 'Listrik, Air & WiFi', 'expense', 'expense', $fixedExpense);
        $insert('5131', 'Belanja Dapur / Sembako', 'expense', 'expense', $fixedExpense);
        $insert('5132', 'Laundry & Kebersihan', 'expense', 'expense', $fixedExpense);
        $insert('5133', 'Gas & Air Galon', 'expense', 'expense', $fixedExpense);
        $insert('5140', 'Iuran Lingkungan / Keamanan', 'expense', 'expense', $fixedExpense);
        $insert('5150', 'Pulsa & Paket Data', 'expense', 'expense', $fixedExpense);

        // Pengeluaran Lifestyle
        $lifestyle = $insert('5200', 'GAYA HIDUP (VARIABLE)', 'expense', 'expense', $expense, false);
        $insert('5210', 'Makan di Luar (Dining)', 'expense', 'expense', $lifestyle);
        $insert('5221', 'Streaming (Netflix/Spotify)', 'expense', 'expense', $lifestyle);
        $insert('5222', 'Hiburan / Bioskop', 'expense', 'expense', $lifestyle);
        $insert('5223', 'Hobi & Games', 'expense', 'expense', $lifestyle);
        $insert('5230', 'Olahraga (Gym/Sport)', 'expense', 'expense', $lifestyle);
        $insert('5241', 'Pakaian & Fashion', 'expense', 'expense', $lifestyle);
        $insert('5242', 'Perawatan Diri (Skincare/Barber)', 'expense', 'expense', $lifestyle);

        // Pengeluaran Pendidikan & Kesehatan
        $social = $insert('5300', 'PENDIDIKAN & KESEHATAN', 'expense', 'expense', $expense, false);
        $insert('5310', 'Pendidikan Anak', 'expense', 'expense', $social);
        $insert('5320', 'Kesehatan / Obat-obatan', 'expense', 'expense', $social);

        // Cicilan Bunga & Adm
        $financialCost = $insert('5400', 'BIAYA FINANSIAL', 'expense', 'expense', $expense, false);
        $insert('5410', 'Bunga Hutang / Cicilan', 'expense', 'expense', $financialCost);
        $insert('5420', 'Biaya Admin Bank', 'expense', 'expense', $financialCost);
        $insert('5430', 'Pajak Kendaraan/PBB', 'expense', 'expense', $financialCost);

        // Pengeluaran Cicilan & Hutang (Cashflow tracing)
        $debtPayments = $insert('5500', 'PENGELUARAN CICILAN & HUTANG', 'expense', 'expense', $expense, false);
        $insert('5510', 'Pembayaran Kartu Kredit', 'expense', 'expense', $debtPayments);
        $insert('5520', 'Pembayaran Paylater', 'expense', 'expense', $debtPayments);
        $insert('5530', 'Cicilan KPR (Pokok+Bunga)', 'expense', 'expense', $debtPayments);
        $insert('5540', 'Cicilan Kendaraan', 'expense', 'expense', $debtPayments);
        $insert('5550', 'Cicilan Pinjaman Lainnya', 'expense', 'expense', $debtPayments);
    }
}
