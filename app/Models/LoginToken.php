<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Einmal-Token für die Anmeldung per Magic Link (Standard Abschnitt 11).
 */
class LoginToken extends Model
{
    public const LIFETIME_MINUTES = 15;

    protected $fillable = ['user_id', 'token_hash', 'expires_at'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Erzeugt ein neues Token und verwirft alle älteren des Nutzers.
     *
     * @return string Klartext-Token für den Link
     */
    public static function issueFor(User $user): string
    {
        static::where('user_id', $user->id)->delete();
        static::where('expires_at', '<', now())->delete();

        $plain = Str::random(64);

        static::create([
            'user_id' => $user->id,
            'token_hash' => static::hash($plain),
            'expires_at' => now()->addMinutes(self::LIFETIME_MINUTES),
        ]);

        return $plain;
    }

    public static function findValid(string $plain): ?self
    {
        return static::where('token_hash', static::hash($plain))
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * Löst das Token ein: gibt den Nutzer zurück und löscht das Token, damit es nur einmal funktioniert.
     */
    public static function consume(string $plain): ?User
    {
        $token = static::findValid($plain);

        if (! $token) {
            return null;
        }

        // delete() liefert die Anzahl gelöschter Zeilen; 0 = parallel schon eingelöst
        if (static::whereKey($token->id)->delete() === 0) {
            return null;
        }

        return $token->user;
    }

    private static function hash(string $plain): string
    {
        return hash('sha256', $plain);
    }
}
