<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('system_settings')->insert([
            ['key' => 'smtp_enabled', 'value' => 'false', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'smtp_host', 'value' => '', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'smtp_port', 'value' => '587', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'smtp_encryption', 'value' => 'tls', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'smtp_username', 'value' => '', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'smtp_password', 'value' => '', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'smtp_from_address', 'value' => '', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'smtp_from_name', 'value' => 'Leafmark', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'turnstile_enabled', 'value' => 'false', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'turnstile_site_key', 'value' => '', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'turnstile_secret_key', 'value' => '', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        DB::table('system_settings')->whereIn('key', [
            'smtp_enabled', 'smtp_host', 'smtp_port', 'smtp_encryption',
            'smtp_username', 'smtp_password', 'smtp_from_address', 'smtp_from_name',
            'turnstile_enabled', 'turnstile_site_key', 'turnstile_secret_key',
        ])->delete();
    }
};
