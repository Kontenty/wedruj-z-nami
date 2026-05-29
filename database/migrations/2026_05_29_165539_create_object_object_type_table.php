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
        Schema::create('object_object_type', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->foreignId('sightseeing_object_id')->constrained()->cascadeOnDelete();
            $table->foreignId('object_type_id')->constrained()->cascadeOnDelete();

            $table->primary(['sightseeing_object_id', 'object_type_id']);
            $table->index('object_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('object_object_type');
    }
};
