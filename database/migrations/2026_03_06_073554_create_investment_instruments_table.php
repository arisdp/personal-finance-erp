<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investment_instruments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('ticker')->unique(); // e.g. BBCA, GOLD, BTC
            $table->string('name');             // e.g. Bank Central Asia Tbk
            $table->string('asset_type');       // stock, gold, crypto, mutual_fund, other
            $table->decimal('current_price', 18, 2)->default(0);
            $table->timestamp('last_price_update')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Add instrument_id to asset_holdings
        Schema::table('asset_holdings', function (Blueprint $table) {
            $table->uuid('instrument_id')->nullable()->after('workspace_id');
            $table->foreign('instrument_id')
                  ->references('id')
                  ->on('investment_instruments')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('asset_holdings', function (Blueprint $table) {
            $table->dropForeign(['instrument_id']);
            $table->dropColumn('instrument_id');
        });
        Schema::dropIfExists('investment_instruments');
    }
};
