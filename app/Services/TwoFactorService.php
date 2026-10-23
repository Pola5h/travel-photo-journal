<?php

namespace App\Services;

use App\Exceptions\TwoFactorAlreadyEnabledException;
use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorService
{
    public function __construct(private readonly Google2FA $google2fa) {}

    /**
     * @return array{secret: string, qr_svg: string}
     */
    public function setup(User $user): array
    {
        if ($user->two_factor_confirmed_at !== null) {
            throw new TwoFactorAlreadyEnabledException;
        }

        $secret = $this->google2fa->generateSecretKey();

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => null,
        ])->save();

        return [
            'secret' => $secret,
            'qr_svg' => $this->qrSvg($user, $secret),
        ];
    }

    public function confirm(User $user, string $otp): void
    {
        if (! $this->verifyCode($user, $otp)) {
            throw ValidationException::withMessages([
                'otp' => ['The one-time code is invalid.'],
            ]);
        }

        $user->forceFill(['two_factor_confirmed_at' => now()])->save();
    }

    public function verifyCode(User $user, string $otp): bool
    {
        $secret = $user->two_factor_secret;

        if ($secret === null) {
            return false;
        }

        return (bool) $this->google2fa->verifyKey($secret, $otp, 1);
    }

    private function qrSvg(User $user, string $secret): string
    {
        $url = $this->google2fa->getQRCodeUrl(config('app.name'), $user->email, $secret);

        $renderer = new ImageRenderer(new RendererStyle(200), new SvgImageBackEnd);

        return (new Writer($renderer))->writeString($url);
    }
}
