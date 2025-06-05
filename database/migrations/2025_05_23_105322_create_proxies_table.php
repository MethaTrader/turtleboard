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
        Schema::create('proxies', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address');
            $table->integer('port');
            $table->string('username')->nullable();
            $table->text('password')->nullable();
            $table->timestamp('last_validation_date')->nullable();
            $table->string('validation_status')->default('pending');
            $table->integer('response_time')->nullable(); // in milliseconds
            $table->string('geolocation')->nullable();
            $table->string('country_code', 3)->nullable(); // Change from 2 to 3 characters
            $table->json('metadata')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            // Add unique constraint on IP and port
            $table->unique(['ip_address', 'port']);
        });

        // Add foreign key to email_accounts table after proxies table exists
        Schema::table('email_accounts', function (Blueprint $table) {
            $table->foreign('proxy_id')->references('id')->on('proxies')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_accounts', function (Blueprint $table) {
            $table->dropForeign(['proxy_id']);
        });

        Schema::dropIfExists('proxies');
    }
};