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
        Schema::create('web3_wallets', function (Blueprint $table) {
            $table->id();
            $table->string('address')->unique();
            $table->text('seed_phrase');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });

        // Add foreign key to mexc_accounts table after web3_wallets table exists
        Schema::table('mexc_accounts', function (Blueprint $table) {
            $table->foreign('web3_wallet_id')->references('id')->on('web3_wallets')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mexc_accounts', function (Blueprint $table) {
            $table->dropForeign(['web3_wallet_id']);
        });

        Schema::dropIfExists('web3_wallets');
    }
};