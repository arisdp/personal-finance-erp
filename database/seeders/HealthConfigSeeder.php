<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Workspace;
use App\Models\HealthConfig;

class HealthConfigSeeder extends Seeder
{
    public function run(): void
    {
        $workspace = Workspace::where('slug', 'keluarga-utama')->first();

        if (!$workspace) return;

        $configs = [
            [
                'metric_key' => 'emergency_fund',
                'metric_label' => 'Emergency Fund',
                'target_value' => 6.00,
                'comparison' => 'gte',
                'unit' => 'months',
                'category' => 'safety',
                'sort_order' => 1,
            ],
            [
                'metric_key' => 'debt_ratio',
                'metric_label' => 'Debt Ratio',
                'target_value' => 50.00,
                'comparison' => 'lte',
                'unit' => 'percent',
                'category' => 'debt',
                'sort_order' => 2,
            ],
            [
                'metric_key' => 'saving_rate',
                'metric_label' => 'Saving Rate',
                'target_value' => 20.00,
                'comparison' => 'gte',
                'unit' => 'percent',
                'category' => 'savings',
                'sort_order' => 3,
            ],
            [
                'metric_key' => 'investment_ratio',
                'metric_label' => 'Investment Ratio',
                'target_value' => 30.00,
                'comparison' => 'gte',
                'unit' => 'percent',
                'category' => 'investment',
                'sort_order' => 4,
            ],
            [
                'metric_key' => 'expense_ratio',
                'metric_label' => 'Expense Ratio',
                'target_value' => 70.00,
                'comparison' => 'lte',
                'unit' => 'percent',
                'category' => 'spending',
                'sort_order' => 5,
            ],
            [
                'metric_key' => 'liquidity_ratio',
                'metric_label' => 'Liquidity Ratio',
                'target_value' => 100.00,
                'comparison' => 'gte',
                'unit' => 'percent',
                'category' => 'safety',
                'sort_order' => 6,
            ],
        ];

        foreach ($configs as $config) {
            HealthConfig::updateOrCreate(
                [
                    'workspace_id' => $workspace->id,
                    'metric_key' => $config['metric_key'],
                ],
                array_merge($config, ['workspace_id' => $workspace->id])
            );
        }
    }
}
