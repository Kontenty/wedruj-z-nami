<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sightseeing_objects', function (Blueprint $table): void {
            $table->string('osm_id', 50)->nullable()->after('source_updated_at');
            $table->string('osm_type', 10)->nullable()->after('osm_id');

            $table->index(['osm_type', 'osm_id']);
        });
    }

    public function down(): void
    {
        Schema::table('sightseeing_objects', function (Blueprint $table): void {
            $table->dropIndex(['osm_type', 'osm_id']);
            $table->dropColumn(['osm_id', 'osm_type']);
        });
    }
};
