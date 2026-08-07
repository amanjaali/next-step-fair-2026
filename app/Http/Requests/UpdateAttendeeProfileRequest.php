<?php

namespace App\Http\Requests;

use App\Models\Registration;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * What somebody may change about their own registration.
 *
 * Not the phone number. That is the identity behind the badge and the account —
 * it was proved with a code, it is where the QR was sent, and letting it be
 * edited from a signed-in session would hand somebody a way to move a stranger's
 * badge to their own handset. Changing it is a conversation with the desk.
 *
 * Everything else is theirs: the spelling of their name, the city they travel
 * from, the language they want to be written to in, what they are studying, and
 * a picture.
 */
class UpdateAttendeeProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('attendee')->check();
    }

    public function rules(): array
    {
        $me = $this->attendee();
        $student = $me->type === Registration::TYPE_STUDENT;

        return [
            'full_name' => ['required', 'string', 'min:3', 'max:190'],
            'city' => ['required', Rule::in(config('nextstep.cities'))],
            'locale' => ['required', Rule::in(array_keys(config('nextstep.locales')))],

            // Uniqueness is checked in withValidator(), against the keyed hash.
            'email' => [Rule::requiredIf($student), 'nullable', 'email', 'max:190'],

            'password' => ['nullable', Password::min(8)],

            'school_name' => ['nullable', 'string', 'max:190'],
            'education_stage' => [
                'nullable',
                Rule::in(array_keys(config('nextstep.education_stages'))),
            ],

            'position' => ['nullable', 'string', 'max:190'],
            'organization' => ['nullable', 'string', 'max:190'],

            // Roughly a phone photo. Bigger than this is a scan or a screenshot,
            // and nothing on the page renders larger than 320px anyway.
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120', 'dimensions:min_width=120,min_height=120'],
            'remove_photo' => ['nullable', 'boolean'],
        ];
    }

    /**
     * A student signs in with their address, so it cannot become somebody else's.
     *
     * The check has to go through the keyed hash: the column itself is encrypted,
     * and encrypted values are different bytes every time, so a plain unique rule
     * would compare ciphertext against a plaintext address and never match.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $email = $this->input('email');

            if (blank($email)) {
                return;
            }

            $taken = Registration::whereEmail($email)
                ->whereKeyNot($this->attendee()->getKey())
                ->exists();

            if ($taken) {
                $validator->errors()->add('email', __('attendee.edit.errors.email_taken'));
            }
        });
    }

    public function messages(): array
    {
        return [
            'full_name.required' => __('register.errors.name'),
            'city.required' => __('register.errors.city'),
            'city.in' => __('register.errors.city'),
            'email.required' => __('register.errors.email'),
            'email.email' => __('register.errors.email'),
            'photo.image' => __('attendee.edit.errors.photo'),
            'photo.mimes' => __('attendee.edit.errors.photo'),
            'photo.max' => __('attendee.edit.errors.photo_large'),
            'photo.dimensions' => __('attendee.edit.errors.photo_small'),
        ];
    }

    private function attendee(): Registration
    {
        /** @var Registration $me */
        $me = Auth::guard('attendee')->user();

        return $me;
    }
}
