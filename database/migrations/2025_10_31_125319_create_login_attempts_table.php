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
        Schema::create('login_attempts', function (Blueprint $table) {
            $table->id('attempt_id');

            $table->unsignedBigInteger('user_id')->nullable();
            $table->dateTime('attempt_time');
            $table->string('attempt_ip', 45)->nullable(); // ipv4/ipv6
            $table->boolean('succes')->default(false);
            $table->string('failure_reason')->nullable();

            $table->foreign('user_id')
                ->references('user_id')->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_attempts');
    }
};
