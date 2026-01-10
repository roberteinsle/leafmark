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
        // Rename shelves table to tags
        Schema::rename('shelves', 'tags');

        // Rename shelf_books pivot table to book_tag
        Schema::rename('shelf_books', 'book_tag');

        // Rename foreign key column in book_tag table
        Schema::table('book_tag', function (Blueprint $table) {
            $table->renameColumn('shelf_id', 'tag_id');
        });

        // Add new fields to books table
        Schema::table('books', function (Blueprint $table) {
            $table->enum('format', [
                'digital',
                'paperback',
                'hardcover',
                'audiobook',
                'magazine',
                'spiral_bound',
                'leather_bound',
                'journal',
                'comic',
                'graphic_novel',
                'manga',
                'box_set',
                'omnibus',
                'reference',
                'other'
            ])->nullable()->after('status');

            $table->date('purchase_date')->nullable()->after('format');
            $table->decimal('purchase_price', 10, 2)->nullable()->after('purchase_date');
            $table->string('purchase_currency', 3)->default('EUR')->after('purchase_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove new fields from books table
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['format', 'purchase_date', 'purchase_price', 'purchase_currency']);
        });

        // Rename foreign key column back
        Schema::table('book_tag', function (Blueprint $table) {
            $table->renameColumn('tag_id', 'shelf_id');
        });

        // Rename tables back
        Schema::rename('book_tag', 'shelf_books');
        Schema::rename('tags', 'shelves');
    }
};
