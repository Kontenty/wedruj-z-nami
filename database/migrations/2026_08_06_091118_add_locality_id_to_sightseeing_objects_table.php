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
        Schema::table('sightseeing_objects', function (Blueprint $table) {
            $table->foreignId('locality_id')->nullable()->after('locality')->constrained('localities')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sightseeing_objects', function (Blueprint $table) {
            $table->dropForeign(['locality_id']);
            $table->dropColumn('locality_id');
        });
    }
};
