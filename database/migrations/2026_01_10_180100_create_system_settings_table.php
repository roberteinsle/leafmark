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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('system_settings')->insert([
            [
                'key' => 'registration_enabled',
                'value' => 'true',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'registration_mode',
                'value' => 'open', // open, domain, invitation, code
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'allowed_email_domains',
                'value' => '', // comma-separated domains
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'registration_code',
                'value' => '', // personal registration code
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
