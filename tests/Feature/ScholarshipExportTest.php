<?php

namespace Tests\Feature;

use App\Filament\Exports\ScholarshipApplicationExporter;
use App\Filament\Exports\ScholarshipContactsExporter;
use App\Filament\Exports\ScholarshipReviewExporter;
use App\Filament\Exports\ScholarshipSummaryExporter;
use App\Filament\Resources\ScholarshipApplications\Pages\ListScholarshipApplications;
use App\Filament\Resources\ScholarshipApplications\ScholarshipApplicationResource;
use App\Models\Registration;
use App\Models\ScholarshipApplication;
use App\Models\User;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ScholarshipExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function superAdmin(): User
    {
        return User::where('email', 'admin@nextstepfair.com')->firstOrFail();
    }

    private function committee(): User
    {
        return User::where('email', 'committee@nextstepfair.com')->firstOrFail();
    }

    private function application(array $attributes = []): ScholarshipApplication
    {
        $student = Registration::create([
            'track' => Registration::TRACK_FAIR,
            'type' => Registration::TYPE_STUDENT,
            'status' => Registration::STATUS_CONFIRMED,
            'locale' => 'en',
            'full_name' => 'Export Student',
            'phone' => '7701239876',
            'phone_country' => '+964',
            'city' => 'Sulaimani',
            'days' => [1, 2, 3],
            'email' => 'export.student@example.com',
            'password' => 'a-good-password',
            'education_stage' => 'grade12',
            'confirmed_at' => now(),
        ]);

        return ScholarshipApplication::create(array_merge([
            'registration_id' => $student->id,
            'cycle' => config('scholarship.cycle'),
            'status' => ScholarshipApplication::STATUS_SUBMITTED,
            'step' => 4,
            'eligibility' => ['year' => 'y', 'funded' => 'n'],
            'eligibility_passed_at' => now(),
            'region_code' => 'SLM',
            'district' => 'Chamchamal',
            'exam_status' => 'published',
            'exam_average' => 92.5,
            'first_choice_university' => 'AUIS',
            'first_choice_department' => 'Computer Science',
            'statement' => 'I want to study computers.',
            'documents' => ['certificate' => 'scholarship/test/certificate.pdf'],
            'submitted_at' => now(),
        ], $attributes));
    }

    /** @return list<string> */
    private function ticked(string $exporter): array
    {
        return collect($exporter::getColumns())
            ->filter(fn (ExportColumn $c) => $c->isEnabledByDefault())
            ->map(fn (ExportColumn $c) => $c->getName())
            ->values()->all();
    }

    public function test_a_super_admin_sees_the_export_button(): void
    {
        Livewire::actingAs($this->superAdmin())
            ->test(ListScholarshipApplications::class)
            ->assertActionVisible('export_summary')
            ->assertActionVisible('export_everything');
    }

    public function test_a_committee_member_who_can_review_cannot_export(): void
    {
        $this->actingAs($this->committee())->get('/admin/scholarship-applications')->assertOk();

        Livewire::actingAs($this->committee())
            ->test(ListScholarshipApplications::class)
            ->assertActionHidden('export_summary')
            ->assertActionHidden('export_everything');
    }

    public function test_each_preset_ticks_the_right_columns(): void
    {
        $this->assertSame(ScholarshipApplicationExporter::PRESETS['summary'], $this->ticked(ScholarshipSummaryExporter::class));
        $this->assertSame(ScholarshipApplicationExporter::PRESETS['contacts'], array_values(array_intersect(
            ScholarshipApplicationExporter::PRESETS['contacts'], $this->ticked(ScholarshipContactsExporter::class),
        )));

        $review = $this->ticked(ScholarshipReviewExporter::class);
        $this->assertContains('doc_judicial_record', $review);
        $this->assertContains('statement_words', $review);
        $this->assertNotContains('statement', $review, 'Full text stays out of the Review preset.');

        $everything = $this->ticked(ScholarshipApplicationExporter::class);
        $this->assertCount(count(ScholarshipApplicationExporter::getColumns()), $everything);
        $this->assertContains('statement', $everything);
        $this->assertContains('committee_notes', $everything);
    }

    public function test_the_everything_export_writes_the_answers_and_decrypted_contact_details(): void
    {
        Storage::fake('local');
        $application = $this->application();

        Livewire::actingAs($this->superAdmin())
            ->test(ListScholarshipApplications::class)
            ->callAction('export_everything', data: ['format' => 'csv']);

        $export = Export::latest('id')->firstOrFail();
        $csv = collect(Storage::disk($export->file_disk)->allFiles($export->getFileDirectory()))
            ->filter(fn (string $f) => str_ends_with($f, '.csv'))
            ->map(fn (string $f) => Storage::disk($export->file_disk)->get($f))
            ->implode('');

        $this->assertStringContainsString('Export Student', $csv);
        $this->assertStringContainsString('964 770 123 9876', $csv);
        $this->assertStringContainsString('export.student@example.com', $csv);
        $this->assertStringContainsString('I want to study computers.', $csv);
        $this->assertStringContainsString('Chamchamal', $csv);
        $this->assertStringContainsString(
            route('admin.scholarship.document', ['application' => $application->id, 'key' => 'certificate']),
            $csv,
        );
    }

    public function test_formula_like_text_is_neutralised_and_labels_are_readable(): void
    {
        Storage::fake('local');
        $this->application([
            'statement' => '=HYPERLINK("http://evil.example","click")',
            'proposal' => '@SUM(1+1)',
            'district' => '-Chamchamal',
            'documents' => ['first_choice_form' => 'scholarship/test/auis-form.pdf'],
        ]);

        Livewire::actingAs($this->superAdmin())
            ->test(ListScholarshipApplications::class)
            ->callAction('export_everything', data: ['format' => 'csv']);

        $export = Export::latest('id')->firstOrFail();
        $csv = collect(Storage::disk($export->file_disk)->allFiles($export->getFileDirectory()))
            ->filter(fn (string $f) => str_ends_with($f, '.csv'))
            ->map(fn (string $f) => Storage::disk($export->file_disk)->get($f))
            ->implode('');

        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringContainsString("'@SUM(1+1)", $csv);
        $this->assertStringContainsString("'-Chamchamal", $csv);
        $this->assertStringNotContainsString(',=HYPERLINK', $csv);
        $this->assertStringNotContainsString('"=HYPERLINK', $csv);

        $this->assertStringContainsString(ScholarshipApplicationResource::statusOptions()['submitted'], $csv);
        $this->assertStringContainsString('/first_choice_form', $csv);
    }

    public function test_the_phone_is_written_so_spreadsheets_keep_it_as_text(): void
    {
        $this->assertSame("'+964", ScholarshipApplicationExporter::safeCell('+964'));
        $this->assertSame('964 770 123 9876', ScholarshipApplicationExporter::safeCell('964 770 123 9876'));
        $this->assertSame(92.5, ScholarshipApplicationExporter::safeCell(92.5));
    }

    public function test_document_links_open_only_for_signed_in_reviewers(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('scholarship/test/certificate.pdf', '%PDF-test');
        $application = $this->application();
        $url = route('admin.scholarship.document', ['application' => $application->id, 'key' => 'certificate']);

        $this->get($url)->assertRedirect(route('filament.admin.auth.login'));

        $this->actingAs(User::where('email', 'editor@nextstepfair.com')->firstOrFail())->get($url)->assertForbidden();

        $this->actingAs($this->committee())->get($url)->assertOk();
        $this->actingAs($this->superAdmin())->get($url)->assertOk();

        $this->actingAs($this->superAdmin())
            ->get(route('admin.scholarship.document', ['application' => $application->id, 'key' => '../../.env']))
            ->assertNotFound();
        $this->actingAs($this->superAdmin())
            ->get(route('admin.scholarship.document', ['application' => $application->id, 'key' => 'residence']))
            ->assertNotFound();
    }
}
