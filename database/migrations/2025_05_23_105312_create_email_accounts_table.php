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
        Schema::create('email_accounts', function (Blueprint $table) {
            $table->id();
            $table->enum('provider', ['Gmail', 'Outlook', 'Yahoo', 'Rambler']);
            $table->string('email_address')->unique();
            $table->text('password');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('proxy_id')->nullable()->unique();
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
        Schema::dropIfExists('email_accounts');
    }
};