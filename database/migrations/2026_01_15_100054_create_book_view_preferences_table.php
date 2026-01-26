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
        Schema::create('book_view_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('shelf')->default('all'); // 'all', 'read', 'currently_reading', 'want_to_read'
            $table->string('view_mode')->default('card'); // 'card' or 'table'
            $table->json('visible_columns')->nullable(); // For Phase 2
            $table->string('sort_field')->default('added_at');
            $table->string('sort_order')->default('desc'); // 'asc' or 'desc'
            $table->integer('per_page')->default(25);
            $table->timestamps();

            // Each user can have one preference per shelf
            $table->unique(['user_id', 'shelf']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_view_preferences');
    }
};
