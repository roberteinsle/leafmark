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
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('recipient');
            $table->string('subject');
            $table->string('type')->default('test'); // test, password_reset, verification, etc.
            $table->enum('status', ['sent', 'failed'])->default('failed');
            $table->text('error_message')->nullable();
            $table->json('smtp_config')->nullable(); // Store SMTP config used at time of sending
            $table->text('stack_trace')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
