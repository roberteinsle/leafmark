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
        Schema::create('book_covers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->string('path'); // Storage path to the image
            $table->boolean('is_primary')->default(false); // Mark the primary/default cover
            $table->integer('sort_order')->default(0); // For custom ordering
            $table->timestamps();

            $table->index(['book_id', 'is_primary']);
            $table->index(['book_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_covers');
    }
};
