<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One OTPIQ / Meta WhatsApp template per logical key and language.
 *
 * The provider id and the exact name approved in the OTPIQ dashboard (`name`)
 * are what the gateway needs at send time; body variable order and the header
 * image flag mirror `config/whatsapp.php` so the admin can edit them in one place.
 */
class OtpiqTemplate extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'body_variables' => 'array',
            'header_image' => 'boolean',
            'active' => 'boolean',
        ];
    }
}
