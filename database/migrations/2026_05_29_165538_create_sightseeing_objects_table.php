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
        Schema::create('sightseeing_objects', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('lead');
            $table->longText('description');
            $table->string('locality');
            $table->boolean('is_unesco')->default(false);
            $table->text('opening_hours')->nullable();
            $table->text('ticket_prices')->nullable();
            $table->text('accessibility')->nullable();
            $table->string('website', 500)->nullable();
            $table->string('data_source')->nullable();
            $table->date('source_updated_at')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->geometry('geometry', 'geometry', 4326);
            $table->foreignId('voivodeship_id')->constrained()->restrictOnDelete();
            $table->string('status')->default('draft');
            $table->boolean('published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index('voivodeship_id');
            $table->index('published');
            $table->index('status');
            $table->index('published_at');
            $table->spatialIndex('geometry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sightseeing_objects');
    }
};
