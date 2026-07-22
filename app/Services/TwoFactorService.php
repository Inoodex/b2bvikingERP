<?php

namespace App\Services;

use App\Mail\TwoFactorCodeMail;
use App\Models\TwoFactorCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class TwoFactorService
{
    private const CODE_LENGTH = 8;
    private const CODE_CHARSET = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789@#$%*';
    private const EXPIRES_MINUTES = 10;

    public function send(User $user): void
    {
        $code = $this->generateCode();

        TwoFactorCode::updateOrCreate(
            ['user_id' => $user->id],
            [
                'code_hash' => Hash::make($code),
                'expires_at' => now()->addMinutes(self::EXPIRES_MINUTES),
            ]
        );

        Mail::to($user->email)->send(new TwoFactorCodeMail($code, self::EXPIRES_MINUTES));
    }

    private function generateCode(): string
    {
        $charset = self::CODE_CHARSET;
        $maxIndex = strlen($charset) - 1;
        $code = '';

        for ($i = 0; $i < self::CODE_LENGTH; $i++) {
            $code .= $charset[random_int(0, $maxIndex)];
        }

        return $code;
    }
}
