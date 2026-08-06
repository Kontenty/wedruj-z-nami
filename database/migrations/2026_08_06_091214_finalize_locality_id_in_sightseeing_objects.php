<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sightseeing_objects', function (Blueprint $table) {
            $table->dropForeign(['locality_id']);
            $table->foreignId('locality_id')->nullable(false)->change();
            $table->foreign('locality_id')->references('id')->on('localities')->cascadeOnDelete();
            $table->dropForeign(['voivodeship_id']);
            $table->dropIndex(['voivodeship_id']);
            $table->dropColumn(['locality', 'voivodeship_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sightseeing_objects', function (Blueprint $table) {
            $table->string('locality')->after('lead');
            $table->foreignId('voivodeship_id')->after('locality')->constrained('voivodeships')->restrictOnDelete();
            $table->foreignId('locality_id')->nullable()->change();
        });
    }
};
