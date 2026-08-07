<?php

namespace App\Filament\Resources\Registrations\Schemas;

use App\Models\Registration;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Manual entry and correction.
 *
 * This is the walk-in form used at the door, and the correction form when a name
 * is misspelled on a badge. Consent is captured explicitly here too — a walk-in
 * still has to agree to WhatsApp delivery before a badge is issued.
 */
class RegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Registrant')
                ->columns(2)
                ->schema([
                    TextInput::make('full_name')
                        ->label(__('admin.fields.name'))
                        ->required()
                        ->maxLength(120)
                        ->columnSpanFull(),

                    Select::make('type')
                        ->label(__('admin.fields.type'))
                        ->options(['student' => 'Student', 'parent' => 'Parent'])
                        ->default('student')
                        ->required(),

                    Select::make('locale')
                        ->label(__('admin.fields.language'))
                        ->options(['en' => 'English', 'ku' => 'Kurdish', 'ar' => 'Arabic'])
                        ->default('ku')
                        ->required(),

                    Select::make('phone_country')
                        ->label('Country code')
                        ->options(array_combine(
                            array_keys(config('nextstep.phone.countries')),
                            array_keys(config('nextstep.phone.countries'))
                        ))
                        ->default(config('nextstep.phone.default_country'))
                        ->required(),

                    TextInput::make('phone')
                        ->label(__('admin.fields.phone'))
                        ->tel()
                        ->required()
                        ->helperText('The badge is delivered to this number on WhatsApp.'),

                    TextInput::make('email')->label(__('admin.fields.email'))->email(),

                    Select::make('city')
                        ->label(__('admin.fields.city'))
                        ->options(array_combine(config('nextstep.cities'), config('nextstep.cities')))
                        ->searchable()
                        ->required(),

                    DatePicker::make('date_of_birth')->label('Date of birth'),

                    Select::make('gender')->options([
                        'male' => 'Male', 'female' => 'Female', 'undisclosed' => 'Prefer not to say',
                    ]),
                ]),

            Section::make('Attendance')
                ->columns(2)
                ->schema([
                    Select::make('days')
                        ->label(__('admin.fields.days'))
                        ->multiple()
                        ->options([1 => 'Day 1', 2 => 'Day 2', 3 => 'Day 3'])
                        ->required(),

                    Select::make('status')
                        ->label(__('admin.fields.status'))
                        ->options([
                            Registration::STATUS_AWAITING_OTP => 'Awaiting OTP',
                            Registration::STATUS_CONFIRMED => 'Confirmed',
                            Registration::STATUS_CHECKED_IN => 'Checked in',
                            Registration::STATUS_CANCELLED => 'Cancelled',
                        ])
                        ->default(Registration::STATUS_CONFIRMED)
                        ->required(),

                    Select::make('current_status')
                        ->label('Current status')
                        ->options([
                            'grade12' => '12th grade student',
                            'graduate' => 'Recent graduate',
                            'university' => 'University student',
                            'other' => 'Other',
                        ])
                        ->visible(fn ($get) => $get('type') === 'student'),

                    TextInput::make('school_name')
                        ->label('School')
                        ->visible(fn ($get) => $get('type') === 'student'),

                    Select::make('relationship')
                        ->options([
                            'father' => 'Father', 'mother' => 'Mother',
                            'guardian' => 'Guardian', 'other' => 'Other',
                        ])
                        ->visible(fn ($get) => $get('type') === 'parent'),

                    Textarea::make('notes')->label('Internal notes')->rows(3)->columnSpanFull(),
                ]),

            Section::make(__('admin.fields.consents'))
                ->columns(3)
                ->schema([
                    Checkbox::make('consents.terms.given')->label('Terms & privacy')->default(true),
                    Checkbox::make('consents.whatsapp.given')->label('WhatsApp updates')->default(true),
                    Checkbox::make('consents.photography.given')->label('Photography'),
                ]),
        ]);
    }
}
