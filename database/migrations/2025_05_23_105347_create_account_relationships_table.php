<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('account_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proxy_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('email_account_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('mexc_account_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('web3_wallet_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_relationships');
    }
};