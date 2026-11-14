<?php

namespace Tests\Feature\Auth;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Standard Abschnitt 11: SQL-Injection-Payloads und Rate-Limiting am Login.
 */
class LoginSecurityTest extends TestCase
{
    public static function injectionPayloads(): array
    {
        return [
            'or-true' => ["' OR '1'='1"],
            'comment' => ["admin'--"],
            'or-true-email' => ["x@example.com' OR '1'='1"],
            'union' => ["' UNION SELECT * FROM users--"],
        ];
    }

    #[DataProvider('injectionPayloads')]
    public function test_injection_payloads_are_rejected_without_db_error(string $payload): void
    {
        $this->createUser(['email' => 'admin@example.com', 'password' => bcrypt('secret-password')]);

        $response = $this->post('/login', ['email' => $payload, 'password' => $payload]);

        $response->assertStatus(302)->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_password_login_is_rate_limited(): void
    {
        $user = $this->createUser(['password' => bcrypt('correct-password')]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'wrong']);
        }

        $this->post('/login', ['email' => $user->email, 'password' => 'correct-password'])
            ->assertTooManyRequests();
        $this->assertGuest();
    }
}
