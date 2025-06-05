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

            $table->string('source')->default('manual'); // 'manual' or 'proxy_ipv4'
            $table->string('proxy_ipv4_id')->nullable(); // ProxyIPV4 ID
            $table->timestamp('purchase_date')->nullable();
            $table->timestamp('expiry_date')->nullable();
            $table->string('protocol')->nullable(); // HTTP/HTTPS, SOCKS5, etc.
            $table->string('country_code')->nullable();

            $table->json('metadata')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

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