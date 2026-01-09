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
            $table->string('amazon_access_key')->nullable()->after('google_books_api_key');
            $table->string('amazon_secret_key')->nullable()->after('amazon_access_key');
            $table->string('amazon_associate_tag')->nullable()->after('amazon_secret_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['amazon_access_key', 'amazon_secret_key', 'amazon_associate_tag']);
        });
    }
};
