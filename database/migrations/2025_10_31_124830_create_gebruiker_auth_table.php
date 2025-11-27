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
        Schema::create('gebruiker_auth', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();

            $table->string('password_hash')->nullable();
            $table->string('auth_provider')->nullable();
            $table->boolean('mfa_enabled')->default(false);
            $table->string('mfa_type')->nullable();
            $table->timestamp('last_mfa_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();

            $table->foreign('user_id')
                ->references('user_id')->on('users')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gebruiker_auth');
    }
};
