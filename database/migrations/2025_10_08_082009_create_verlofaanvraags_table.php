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
        Schema::create('verlofaanvraag', function (Blueprint $table) {
            $table->id('aanvraag_id');


            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                ->references('user_id')->on('users')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('verlof_type_id');
            $table->foreign('verlof_type_id')
                ->references('verlof_type_id')
                ->on('verloftype')
                ->restrictOnDelete();

            $table->date('start_datum');
            $table->date('eind_datum');
            $table->text('reden');
            $table->dateTime('aanvraag_datum');
            $table->string('status')->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verlofaanvraags');
    }
};
