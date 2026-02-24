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
        DB::table('accounts')->truncate();

        $now = Carbon::now();

        // Helper function
        $insert = function ($code, $name, $type, $parentId = null, $isPostable = true, $creditLimit = null, $trackLimit = false) use ($now) {
            $id = Str::uuid()->toString();

            DB::table('accounts')->insert([
                'id' => $id,
                'code' => $code,
                'name' => $name,
                'type' => $type,
                'parent_id' => $parentId,
                'is_postable' => $isPostable,
                'credit_limit' => $creditLimit,
                'track_limit' => $trackLimit,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return $id;
        };

        /*
        |--------------------------------------------------------------------------
        | ASSETS
        |--------------------------------------------------------------------------
        */

        $assets = $insert('1000', 'ASSETS', 'asset', null, false);

        // Current Assets
        $currentAssets = $insert('1100', 'Current Assets', 'asset', $assets, false);
        $insert('1110', 'Kas Tunai', 'asset', $currentAssets);
        $insert('1120', 'Bank Utama', 'asset', $currentAssets);
        $insert('1130', 'Bank Tambahan', 'asset', $currentAssets);
        $insert('1140', 'E-Wallet Utama', 'asset', $currentAssets);

        // Emergency Fund
        $emergency = $insert('1200', 'Emergency Fund', 'asset', $assets, false);
        $insert('1210', 'Dana Darurat Bank', 'asset', $emergency);
        $insert('1220', 'Dana Darurat Cash', 'asset', $emergency);
        $insert('1230', 'Dana Darurat Deposito', 'asset', $emergency);

        // Investment
        $investment = $insert('1300', 'Investment Assets', 'asset', $assets, false);
        $insert('1310', 'Emas Fisik', 'asset', $investment);
        $insert('1320', 'Emas Digital', 'asset', $investment);
        $insert('1330', 'Saham', 'asset', $investment);
        $insert('1340', 'Reksadana', 'asset', $investment);
        $insert('1350', 'Crypto', 'asset', $investment);
        $insert('1360', 'Deposito Investasi', 'asset', $investment);
        $insert('1370', 'Obligasi / SBN', 'asset', $investment);
        $insert('1380', 'Properti Investasi', 'asset', $investment);

        // Fixed Assets
        $fixed = $insert('1400', 'Fixed Assets', 'asset', $assets, false);
        $insert('1410', 'Rumah', 'asset', $fixed);
        $insert('1420', 'Kendaraan', 'asset', $fixed);
        $insert('1430', 'Peralatan Elektronik', 'asset', $fixed);
        $insert('1440', 'Tanah', 'asset', $fixed);

        /*
        |--------------------------------------------------------------------------
        | LIABILITIES
        |--------------------------------------------------------------------------
        */

        $liabilities = $insert('2000', 'LIABILITIES', 'liability', null, false);

        // Short Term
        $shortTerm = $insert('2100', 'Short Term Liabilities', 'liability', $liabilities, false);
        $insert('2110', 'Kartu Kredit Utama', 'liability', $shortTerm, true, 20000000, true);
        $insert('2120', 'Kartu Kredit Tambahan', 'liability', $shortTerm, true, 10000000, true);
        $insert('2130', 'Paylater Shopee', 'liability', $shortTerm, true, 5000000, true);
        $insert('2140', 'Paylater Tokopedia', 'liability', $shortTerm, true, 5000000, true);

        // Long Term
        $longTerm = $insert('2200', 'Long Term Liabilities', 'liability', $liabilities, false);
        $insert('2210', 'KPR', 'liability', $longTerm);
        $insert('2220', 'Kredit Kendaraan', 'liability', $longTerm);
        $insert('2230', 'Pinjaman Keluarga', 'liability', $longTerm);
        $insert('2240', 'Pinjaman Bank', 'liability', $longTerm);

        /*
        |--------------------------------------------------------------------------
        | EQUITY
        |--------------------------------------------------------------------------
        */

        $equity = $insert('3000', 'EQUITY', 'equity', null, false);
        $insert('3100', 'Modal Awal Keluarga', 'equity', $equity);
        $insert('3200', 'Laba Ditahan', 'equity', $equity);
        $insert('3300', 'Penarikan Pribadi', 'equity', $equity);

        /*
        |--------------------------------------------------------------------------
        | INCOME
        |--------------------------------------------------------------------------
        */

        $income = $insert('4000', 'INCOME', 'income', null, false);
        $insert('4100', 'Gaji Suami', 'income', $income);
        $insert('4110', 'Gaji Istri', 'income', $income);
        $insert('4120', 'Bonus', 'income', $income);
        $insert('4130', 'Dividen', 'income', $income);
        $insert('4140', 'Profit Saham', 'income', $income);
        $insert('4150', 'Profit Crypto', 'income', $income);
        $insert('4160', 'Sewa Properti', 'income', $income);
        $insert('4170', 'Pendapatan Lain', 'income', $income);

        /*
        |--------------------------------------------------------------------------
        | EXPENSE
        |--------------------------------------------------------------------------
        */

        $expense = $insert('5000', 'EXPENSE', 'expense', null, false);

        // Rumah Tangga
        $rumahTangga = $insert('5100', 'Rumah Tangga', 'expense', $expense, false);
        $insert('5110', 'Belanja Bulanan', 'expense', $rumahTangga);
        $insert('5120', 'Listrik', 'expense', $rumahTangga);
        $insert('5130', 'Air', 'expense', $rumahTangga);
        $insert('5140', 'Internet', 'expense', $rumahTangga);
        $insert('5150', 'Gas', 'expense', $rumahTangga);

        // Pendidikan
        $pendidikan = $insert('5200', 'Pendidikan', 'expense', $expense, false);
        $insert('5210', 'SPP', 'expense', $pendidikan);
        $insert('5220', 'Buku', 'expense', $pendidikan);
        $insert('5230', 'Les', 'expense', $pendidikan);

        // Transportasi
        $transport = $insert('5300', 'Transportasi', 'expense', $expense, false);
        $insert('5310', 'BBM', 'expense', $transport);
        $insert('5320', 'Servis Kendaraan', 'expense', $transport);
        $insert('5330', 'Parkir', 'expense', $transport);

        // Gaya Hidup
        $gayaHidup = $insert('5400', 'Gaya Hidup', 'expense', $expense, false);
        $insert('5410', 'Makan di Luar', 'expense', $gayaHidup);
        $insert('5420', 'Hiburan', 'expense', $gayaHidup);
        $insert('5430', 'Liburan', 'expense', $gayaHidup);
        $insert('5440', 'Belanja Pribadi', 'expense', $gayaHidup);

        // Cicilan
        $cicilan = $insert('5500', 'Cicilan & Hutang', 'expense', $expense, false);
        $insert('5510', 'Cicilan KPR', 'expense', $cicilan);
        $insert('5520', 'Cicilan Kendaraan', 'expense', $cicilan);
        $insert('5530', 'Pembayaran Kartu Kredit', 'expense', $cicilan);
        $insert('5540', 'Pembayaran Paylater', 'expense', $cicilan);
    }
}
