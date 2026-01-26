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
        Schema::create('import_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('source')->default('goodreads'); // goodreads, csv, etc.
            $table->string('filename')->nullable();
            $table->integer('total_rows')->default(0);
            $table->integer('imported_count')->default(0);
            $table->integer('skipped_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->json('errors')->nullable(); // Array of errors with line numbers
            $table->string('import_tag')->nullable(); // e.g., import1768496916845
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('import_tag');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_history');
    }
};
