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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('action_type', ['create', 'update', 'delete']);
            $table->enum('entity_type', [
                'user',
                'email_account',
                'proxy',
                'mexc_account',
                'web3_wallet',
                'balance', // For future balance activities
                'kpi_goal' // For future KPI activities
            ]);
            $table->unsignedBigInteger('entity_id')->nullable(); // Nullable for user registration
            $table->string('description');
            $table->json('metadata')->nullable(); // For storing additional data
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'created_at']);
            $table->index(['entity_type', 'entity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};