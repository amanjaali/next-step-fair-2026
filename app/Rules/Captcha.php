<?php

namespace App\Rules;

use App\Services\CaptchaService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** The typed code has to match the picture that was shown. */
class Captcha implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! app(CaptchaService::class)->check(is_string($value) ? $value : null)) {
            $fail(__('register.errors.captcha'));
        }
    }
}
