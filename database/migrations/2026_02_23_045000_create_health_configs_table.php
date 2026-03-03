<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workspace_id');
            $table->string('metric_key')->index();         // emergency_fund, debt_ratio, etc.
            $table->string('metric_label');                 // "Emergency Fund Ratio"
            $table->decimal('target_value', 8, 2);          // 6.00 (bulan), 50.00 (%)
            $table->string('comparison')->default('gte');    // gte, lte, eq
            $table->string('unit')->default('percent');      // percent, months, ratio
            $table->string('category')->default('general');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->cascadeOnDelete();
            $table->unique(['workspace_id', 'metric_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_configs');
    }
};
