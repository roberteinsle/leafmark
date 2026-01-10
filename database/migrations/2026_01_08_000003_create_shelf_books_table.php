<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shelf_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shelf_id')->constrained()->onDelete('cascade');
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->timestamp('added_at')->useCurrent();
            $table->timestamps();

            // Prevent duplicate entries
            $table->unique(['shelf_id', 'book_id']);
            $table->index(['book_id', 'added_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shelf_books');
    }
};
