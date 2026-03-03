<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_holdings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workspace_id');
            $table->uuid('account_id');
            $table->string('asset_type');           // gold, stock, crypto, mutual_fund, bond
            $table->string('ticker')->nullable();
            $table->string('asset_name');            // "Emas Antam", "BBCA", "Bitcoin"
            $table->decimal('quantity', 18, 6);      // 0.5 gram, 100 lot
            $table->decimal('avg_buy_price', 18, 2);
            $table->decimal('current_price', 18, 2)->default(0);
            $table->date('last_updated')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->cascadeOnDelete();
            $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['workspace_id', 'asset_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_holdings');
    }
};
