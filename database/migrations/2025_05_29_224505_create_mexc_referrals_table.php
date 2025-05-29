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
        Schema::create('mexc_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inviter_account_id')->constrained('mexc_accounts')->onDelete('cascade');
            $table->foreignId('invitee_account_id')->constrained('mexc_accounts')->onDelete('cascade');
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->boolean('inviter_rewarded')->default(false);
            $table->boolean('invitee_rewarded')->default(false);
            $table->decimal('deposit_amount', 10, 2)->nullable();
            $table->timestamp('deposit_date')->nullable();
            $table->timestamp('withdrawal_date')->nullable();
            $table->string('promotion_period')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            // Ensure an invitee can only be invited once
            $table->unique('invitee_account_id');

            // Add indexes for better performance
            $table->index('inviter_account_id');
            $table->index('status');
            $table->index('promotion_period');
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