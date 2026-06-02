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
            $table->string('role')->nullable()->after('password')->index();
        });

        Schema::table('sightseeing_objects', function (Blueprint $table) {
            $table->foreignId('author_id')
                ->nullable()
                ->after('voivodeship_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->index('author_id');
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->foreignId('author_id')
                ->nullable()
                ->after('published_at')
                ->constrained('users')
                ->nullOnDelete();

            $table->boolean('is_featured')->default(false)->after('author_id');
            $table->index('author_id');
            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['author_id']);
            $table->dropIndex(['author_id']);
            $table->dropIndex(['is_featured']);
            $table->dropColumn(['author_id', 'is_featured']);
        });

        Schema::table('sightseeing_objects', function (Blueprint $table) {
            $table->dropForeign(['author_id']);
            $table->dropIndex(['author_id']);
            $table->dropColumn('author_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn('role');
        });
    }
};
