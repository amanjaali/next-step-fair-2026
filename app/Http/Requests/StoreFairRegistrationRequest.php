<?php

namespace App\Http\Requests;

use App\Models\Registration;
use App\Services\CaptchaService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
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

            ...app(CaptchaService::class)->validationRules(),
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
            'captcha.required' => __('register.errors.captcha'),
        ];
    }

    /**
     * The already-registered phone number, if this is a duplicate.
     *
     * A shared number is not always one person: the desk lets a family register
     * several people — more than one of them a student — against a single phone
     * (see RegistrationDeskController::validatedMembers()). Picking an arbitrary
     * row among several would let one sibling's submission silently overwrite
     * another's record instead of completing their own, so more than one match
     * is only resolved when the submitted name points at exactly one of them.
     * Anything less certain falls through to a fresh registration rather than a
     * guess.
     */
    public function existingRegistration(): ?Registration
    {
        $matches = Registration::fair()
            ->active()
            ->wherePhone($this->normalisedPhone())
            // Someone completing their own visitor pass is not a duplicate of
            // themselves; without this they would be bounced off their own number.
            ->when($this->upgrading(), fn ($query, $pass) => $query->whereKeyNot($pass->getKey()))
            ->get();

        if ($matches->count() <= 1) {
            return $matches->first();
        }

        $submittedName = $this->normalisedName((string) $this->input('full_name'));

        $named = $matches->filter(
            fn (Registration $candidate) => $this->normalisedName((string) $candidate->full_name) === $submittedName
        );

        return $named->count() === 1 ? $named->first() : null;
    }

    /**
     * Case-folded, whitespace-collapsed, and Arabic-script-normalized so a
     * name typed on an Arabic keyboard still matches the same name typed on
     * a Kurdish or Farsi one — different keyboards render visually identical
     * letters (yeh, kaf, the alef-hamza forms) on different code points, and
     * diacritics present on one entry and not the other are not a real
     * difference. `ه`/`ە` are never merged: distinct letters, distinct
     * sounds, in Sorani Kurdish.
     */
    private function normalisedName(string $name): string
    {
        $name = trim($name);

        if (class_exists(\Normalizer::class)) {
            $name = \Normalizer::normalize($name, \Normalizer::FORM_C) ?: $name;
        }

        // Diacritics (tashkeel) and the tatweel elongation mark carry no
        // letter identity of their own.
        $name = preg_replace('/[\x{0610}-\x{061A}\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}\x{0640}]/u', '', $name);

        $name = strtr($name, [
            'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ٱ' => 'ا',
            'ی' => 'ي', 'ى' => 'ي',
            'ک' => 'ك',
            'ة' => 'ه',
        ]);

        $name = preg_replace('/\s+/u', ' ', $name);

        return Str::of(trim($name))->lower()->toString();
    }

    /**
     * The same address already used by another account.
     *
     * Not scoped to the fair track: a student signs in with their address, so
     * it cannot become somebody else's — including a conference delegate's,
     * or a badge would silently sit on two unrelated registrations that share
     * an email, and signing in would have no reliable way to tell them apart.
     * Same reasoning as UpdateAttendeeProfileRequest::withValidator().
     */
    public function existingEmail(): ?Registration
    {
        if (blank($this->input('email'))) {
            return null;
        }

        return Registration::active()
            ->whereEmail((string) $this->input('email'))
            ->when($this->upgrading(), fn ($query, $pass) => $query->whereKeyNot($pass->getKey()))
            ->first();
    }

    /**
     * The incomplete record this submission completes, if one is signed in.
     *
     * A visitor pass, or a desk-issued student walk-in with no password yet, is
     * a real registration with just a name and phone on it. When its holder
     * comes back to fill in the rest, the answer belongs on that record — same
     * ticket, same QR — not on a second one.
     */
    public function upgrading(): ?Registration
    {
        if (! $this->upgradeResolved) {
            $attendee = $this->user('attendee');

            $this->upgrade = $attendee instanceof Registration && $attendee->isIncomplete()
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
