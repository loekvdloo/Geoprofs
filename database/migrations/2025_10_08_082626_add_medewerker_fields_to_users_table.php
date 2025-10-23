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
        Schema::table('users', function (Blueprint $table) {
            $table->string('voornaam')->nullable();
            $table->string('achternaam')->nullable();

            $table->unsignedBigInteger('afdeling_id')->nullable();
            $table->foreign('afdeling_id')->references('afdeling_id')->on('afdeling')->nullOnDelete();

            $table->string('role')->default('werknemer');
            $table->string('status')->default('actief');

            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('verlofsaldo')->default(25);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['afdeling_id']);
            $table->dropForeign(['manager_id']);
            $table->dropColumn(['voornaam','achternaam','afdeling_id','role','status','manager_id','verlofsaldo']);
        });
    }
};
