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
        Schema::create('object_collection_sightseeing_object', function (Blueprint $table) {
            $table->foreignId('object_collection_id');
            $table->foreignId('sightseeing_object_id');
            $table->unsignedInteger('position');
            $table->timestamps();

            $table->foreign('object_collection_id', 'ocso_collection_fk')
                ->references('id')
                ->on('object_collections')
                ->cascadeOnDelete();
            $table->foreign('sightseeing_object_id', 'ocso_sightseeing_object_fk')
                ->references('id')
                ->on('sightseeing_objects')
                ->cascadeOnDelete();
            $table->primary(['object_collection_id', 'sightseeing_object_id']);
            $table->index(['object_collection_id', 'position'], 'ocso_collection_position_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('object_collection_sightseeing_object');
    }
};
