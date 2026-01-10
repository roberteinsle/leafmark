<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Basic book information
            $table->string('title');
            $table->string('author')->nullable();
            $table->string('isbn')->nullable()->index();
            $table->string('isbn13')->nullable()->index();
            $table->string('publisher')->nullable();
            $table->date('published_date')->nullable();

            // Description and metadata
            $table->text('description')->nullable();
            $table->integer('page_count')->nullable();
            $table->string('language', 10)->nullable();
            $table->string('cover_url')->nullable();
            $table->string('thumbnail')->nullable();

            // Reading progress
            $table->integer('current_page')->default(0);
            $table->enum('status', ['want_to_read', 'currently_reading', 'read'])->default('want_to_read');

            // Timestamps for reading tracking
            $table->timestamp('added_at')->useCurrent();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            // API tracking
            $table->string('api_source')->nullable(); // 'google', 'openlibrary'
            $table->string('external_id')->nullable();

            $table->timestamps();

            // Indexes for common queries
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'added_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
