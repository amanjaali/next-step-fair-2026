<?php

namespace App\Http\Requests;

use App\Models\Registration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Conference RSVP: who you are, where you work, how to reach you.
 *
 * Seven fields and a tick box. Everything the protocol team used to ask for on
 * this form — dietary requirements, interpretation, delegation size, invitation
 * letters, press accreditation — is a conversation to have with the handful of
 * delegates it concerns, not a wall of questions in front of everybody else.
 */
class StoreConferenceRsvpRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(Registration::conferenceTypes())],
            'full_name' => ['required', 'string', 'min:3', 'max:120'],
            'position' => ['required', 'string', 'max:120'],
            // An individual attends as themselves and has nothing to put here.
            'organization' => [Rule::requiredIf($this->input('type') !== Registration::TYPE_INDIVIDUAL), 'nullable', 'string', 'max:190'],
            'email' => ['required', 'email', 'max:190'],
            'phone_country' => ['required', 'string', 'max:8'],
            'phone' => ['required', 'string', 'regex:/^0?[0-9]{9,12}$/'],
            'city' => ['required', Rule::in(config('nextstep.cities'))],
            'locale' => ['required', Rule::in(array_keys(config('nextstep.locales')))],

            'consent_terms' => ['accepted'],

        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => __('rsvp.errors.name'),
            'position.required' => __('rsvp.errors.position'),
            'organization.required' => __('rsvp.errors.organization'),
            'email.required' => __('rsvp.errors.email'),
            'email.email' => __('rsvp.errors.email'),
            'phone.required' => __('rsvp.errors.phone'),
            'phone.regex' => __('rsvp.errors.phone'),
            'city.required' => __('rsvp.errors.city'),
            'consent_terms.accepted' => __('rsvp.errors.consent'),
        ];
    }

    public function existingRegistration(): ?Registration
    {
        return Registration::conference()
            ->active()
            ->whereEmail((string) $this->input('email'))
            ->first();
    }

    /**
     * A free-mail address routes the RSVP to the protocol team for review
     * rather than auto-issuing a badge.
     */
    public function isFreeMail(): bool
    {
        $domain = strtolower((string) substr(strrchr((string) $this->input('email'), '@') ?: '', 1));

        foreach (config('nextstep.free_mail_domains') as $free) {
            if (str_starts_with($domain, $free.'.')) {
                return true;
            }
        }

        return false;
    }

    public function normalisedPhone(): string
    {
        return ltrim(preg_replace('/\D/', '', (string) $this->input('phone')), '0');
    }
}
