<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing local_cover_path to book_covers table
        $books = DB::table('books')
            ->whereNotNull('local_cover_path')
            ->select('id', 'local_cover_path')
            ->get();

        foreach ($books as $book) {
            DB::table('book_covers')->insert([
                'book_id' => $book->id,
                'path' => $book->local_cover_path,
                'is_primary' => true,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse - we keep the old data intact
    }
};
