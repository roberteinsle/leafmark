<?php

namespace App\Services;

use App\Models\User;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

/**
 * TOTP-Zwei-Faktor für den Passwort-Login (Standard Abschnitt 11, Rang 3).
 */
class TwoFactorService
{
    private const RECOVERY_CODE_COUNT = 8;

    public function __construct(private Google2FA $google2fa)
    {
    }

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey(32);
    }

    public function qrCodeSvg(User $user, string $secret): string
    {
        $url = $this->google2fa->getQRCodeUrl(config('app.name', 'Leafmark'), $user->email, $secret);

        $svg = (new Writer(new ImageRenderer(
            new RendererStyle(192, 0, null, null, Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(17, 24, 39))),
            new SvgImageBackEnd
        )))->writeString($url);

        return trim(substr($svg, strpos($svg, "\n") + 1));
    }

    /**
     * Prüft einen 6-stelligen Code. Jeder Code ist pro Nutzer nur einmal gültig (Schutz vor Replay).
     */
    public function verify(string $secret, string $code, int|string $userKey): bool
    {
        $code = preg_replace('/\s+/', '', $code);

        if (! preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        if (! $this->google2fa->verifyKey($secret, $code, 1)) {
            return false;
        }

        // Cache::add schlägt fehl, wenn der Code in den letzten 2 Minuten schon benutzt wurde
        return Cache::add("2fa-used:{$userKey}:{$code}", true, now()->addMinutes(2));
    }

    /**
     * @return array<int, string>
     */
    public function generateRecoveryCodes(): array
    {
        return collect(range(1, self::RECOVERY_CODE_COUNT))
            ->map(fn () => Str::lower(Str::random(5).'-'.Str::random(5)))
            ->all();
    }

    /**
     * Löst einen Wiederherstellungscode ein; er ist danach verbraucht.
     */
    public function useRecoveryCode(User $user, string $code): bool
    {
        $code = Str::lower(trim($code));
        $codes = $user->two_factor_recovery_codes ?? [];

        $index = array_search($code, $codes, true);
        if ($index === false) {
            return false;
        }

        unset($codes[$index]);
        $user->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save();

        return true;
    }

    public function enable(User $user, string $secret): array
    {
        $codes = $this->generateRecoveryCodes();

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $codes,
            'two_factor_confirmed_at' => now(),
        ])->save();

        return $codes;
    }

    public function disable(User $user): void
    {
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
    }
}
