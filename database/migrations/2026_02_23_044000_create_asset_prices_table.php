<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_prices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->string('asset_type');        // gold, stock, crypto, mutual_fund, bond
            $table->string('ticker')->nullable(); // BBCA, BTC, XAUIDR
            $table->decimal('price', 18, 2);
            $table->date('price_date');
            $table->string('source')->default('manual'); // manual, api
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->index(['account_id', 'price_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_prices');
    }
};
