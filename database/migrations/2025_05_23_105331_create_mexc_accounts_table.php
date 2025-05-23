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
        Schema::create('mexc_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_account_id')->unique()->constrained()->onDelete('cascade');
            $table->text('password');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('web3_wallet_id')->nullable()->unique();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mexc_accounts');
    }
};