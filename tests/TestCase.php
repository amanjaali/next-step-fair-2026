<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;

abstract class TestCase extends BaseTestCase
{
    /**
     * A solved picture code, for any test that submits a registration form.
     *
     * A test cannot read the picture, so it writes the challenge instead: the
     * same hash the service would have stored, and the code that answers it.
     * The service is left to check it exactly as it does in production, which
     * is the part worth exercising.
     */
    protected function captcha(string $code = 'NS4TX'): array
    {
        $this->withSession([
            'captcha' => [
                'hash' => Hash::make($code),
                'expires_at' => now()->addMinutes(20)->timestamp,
            ],
        ]);

        return ['captcha' => $code];
    }
}
