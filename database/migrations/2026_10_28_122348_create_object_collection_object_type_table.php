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
        Schema::create('object_collection_object_type', function (Blueprint $table) {
            $table->foreignId('object_collection_id')
                ->constrained(indexName: 'ocot_collection_fk')
                ->cascadeOnDelete();
            $table->foreignId('object_type_id')
                ->constrained(indexName: 'ocot_type_fk')
                ->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['object_collection_id', 'object_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('object_collection_object_type');
    }
};
