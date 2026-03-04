<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workspace_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('amount', 18, 2);
            $table->uuid('debit_account_id');
            $table->uuid('credit_account_id');
            $table->string('frequency')->default('monthly'); // monthly, weekly, yearly
            $table->integer('day_of_month')->nullable();
            $table->date('next_due_date');
            $table->date('last_posted_date')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->cascadeOnDelete();
            $table->foreign('debit_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('credit_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_transactions');
    }
};
