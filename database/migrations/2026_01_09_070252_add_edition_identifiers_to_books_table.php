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
        Schema::table('books', function (Blueprint $table) {
            // Edition identifiers
            $table->string('openlibrary_edition_id')->nullable()->after('external_id');
            $table->string('goodreads_id')->nullable()->after('openlibrary_edition_id');
            $table->string('librarything_id')->nullable()->after('goodreads_id');

            // OpenLibrary edition URL for easy updates
            $table->string('openlibrary_url', 500)->nullable()->after('librarything_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn([
                'openlibrary_edition_id',
                'goodreads_id',
                'librarything_id',
                'openlibrary_url',
            ]);
        });
    }
};
