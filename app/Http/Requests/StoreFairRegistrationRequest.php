<?php

namespace App\Http\Requests;

use App\Models\Registration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Expo registration: students and parents, one short form each.
 *
 * A student is creating an account, so we ask for an email and a password and for
 * the one academic fact the rest of the programme turns on — whether they are
 * finishing school. A parent is not creating anything: a name, a number and a city
 * is the whole form.
 *
 * Everything that used to be here — gender, stream, subject chips, reasons for
 * coming, which days, where you heard about us, which seminars — is either asked
 * later, once, in the place it matters, or not worth asking at all.
 */
class StoreFairRegistrationRequest extends FormRequest
{
    private ?Registration $upgrade = null;

    private bool $upgradeResolved = false;

    public function isStudent(): bool
    {
        return $this->input('type') === Registration::TYPE_STUDENT;
    }

    public function rules(): array
    {
        $student = $this->isStudent();

        return [
            'type' => ['required', Rule::in([Registration::TYPE_STUDENT, Registration::TYPE_PARENT])],
            'full_name' => ['required', 'string', 'min:3', 'max:120'],
            'phone_country' => ['required', 'string', 'max:8'],
            // Iraqi mobile numbers are 10 digits with the leading zero, 9 without.
            'phone' => ['required', 'string', 'regex:/^0?[0-9]{9,12}$/'],
            'city' => ['required', Rule::in(config('nextstep.cities'))],
            'locale' => ['required', Rule::in(array_keys(config('nextstep.locales')))],

            // The account. Students only.
            'email' => [Rule::requiredIf($student), 'nullable', 'email', 'max:190'],
            'password' => [Rule::requiredIf($student && ! $this->upgrading()?->isStudentAccount()), 'nullable', Password::min(8)],
            'date_of_birth' => [Rule::requiredIf($student), 'nullable', 'date', 'before:today', 'after:1930-01-01'],
            'education_stage' => [Rule::requiredIf($student), 'nullable', Rule::in(array_keys(config('nextstep.education_stages')))],
            'school_name' => ['nullable', 'string', 'max:190'],

            'consent_terms' => ['accepted'],

        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => __('register.errors.name'),
            'phone.required' => __('register.errors.phone'),
            'phone.regex' => __('register.errors.phone'),
            'city.required' => __('register.errors.city'),
            'city.in' => __('register.errors.city'),
            'locale.required' => __('register.errors.pref_lang'),
            'email.required' => __('register.errors.email'),
            'email.email' => __('register.errors.email'),
            'password.required' => __('register.errors.password'),
            'date_of_birth.required' => __('register.errors.dob'),
            'education_stage.required' => __('register.errors.stage'),
            'consent_terms.accepted' => __('register.errors.terms'),
        ];
    }

    /** The already-registered phone number, if this is a duplicate. */
    public function existingRegistration(): ?Registration
    {
        return Registration::fair()
            ->active()
            ->wherePhone($this->normalisedPhone())
            // Someone completing their own visitor pass is not a duplicate of
            // themselves; without this they would be bounced off their own number.
            ->when($this->upgrading(), fn ($query, $pass) => $query->whereKeyNot($pass->getKey()))
            ->first();
    }

    /** The same address already used by another account. */
    public function existingEmail(): ?Registration
    {
        if (blank($this->input('email'))) {
            return null;
        }

        return Registration::fair()
            ->active()
            ->whereEmail((string) $this->input('email'))
            ->when($this->upgrading(), fn ($query, $pass) => $query->whereKeyNot($pass->getKey()))
            ->first();
    }

    /**
     * The visitor pass this submission completes, if one is signed in.
     *
     * A quick pass is a real registration with only a name and a verified phone on
     * it. When its holder comes back to fill in the rest, the answer belongs on
     * that record — same ticket, same QR — not on a second one.
     */
    public function upgrading(): ?Registration
    {
        if (! $this->upgradeResolved) {
            $attendee = $this->user('attendee');

            $this->upgrade = $attendee instanceof Registration && $attendee->isQuickPass()
                ? $attendee
                : null;
            $this->upgradeResolved = true;
        }

        return $this->upgrade;
    }

    public function normalisedPhone(): string
    {
        return ltrim(preg_replace('/\D/', '', (string) $this->input('phone')), '0');
    }
}
