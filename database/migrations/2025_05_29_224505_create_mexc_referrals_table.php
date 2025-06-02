<?php
// database/migrations/2025_05_29_224505_create_mexc_referrals_table.php

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
        Schema::create('mexc_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inviter_account_id')->constrained('mexc_accounts')->onDelete('cascade');
            $table->foreignId('invitee_account_id')->constrained('mexc_accounts')->onDelete('cascade');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            // Ensure an invitee can only be invited once
            $table->unique('invitee_account_id');

            // Ensure the same pair cannot have multiple referrals
            $table->unique(['inviter_account_id', 'invitee_account_id']);

            // Add indexes for better performance
            $table->index('inviter_account_id');
            $table->index('status');
            $table->string('referral_link')->nullable();
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mexc_referrals');
    }
};