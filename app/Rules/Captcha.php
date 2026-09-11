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
        $service = app(CaptchaService::class);

        if (! $service->enabled()) {
            return;
        }

        if (! $service->check(is_string($value) ? $value : null)) {
            $fail(__('register.errors.captcha'));
        }
    }
}
