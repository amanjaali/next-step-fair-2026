<?php

namespace Tests\Feature;

use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Editing your own registration, and putting a face to it.
 *
 * The rule that carries the most weight here is the one about the phone number:
 * it is the identity behind the badge, it was proved with a code, and nothing
 * on this screen may move it.
 */
class AttendeeProfileEditTest extends TestCase
{
    use RefreshDatabase;

    private function student(array $attributes = []): Registration
    {
        return Registration::create(array_merge([
            'track' => Registration::TRACK_FAIR,
            'type' => Registration::TYPE_STUDENT,
            'status' => Registration::STATUS_CONFIRMED,
            'locale' => 'en',
            'full_name' => 'Hemin Karim Salih',
            'phone' => '7719995101',
            'phone_country' => '+964',
            'city' => 'Sulaimani',
            'email' => 'hemin.edit@example.com',
            'password' => 'a-good-password',
            'education_stage' => 'grade12',
            'days' => [1, 2, 3],
            'verified_at' => now(),
            'confirmed_at' => now(),
        ], $attributes));
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'full_name' => 'Hemin Karim Salih',
            'city' => 'Erbil',
            'locale' => 'ku',
            'email' => 'hemin.edit@example.com',
        ], $overrides);
    }

    public function test_the_form_opens_in_every_language(): void
    {
        $student = $this->student();

        foreach (['en', 'ku', 'ar'] as $locale) {
            $this->actingAs($student, 'attendee')->get("/{$locale}/me/edit")->assertSuccessful();
        }
    }

    public function test_a_stranger_cannot_open_it(): void
    {
        $this->get('/en/me/edit')->assertRedirect();
        $this->post('/en/me/edit', $this->payload())->assertRedirect('/en/signin');
    }

    public function test_saved_details_come_back_on_the_page(): void
    {
        $student = $this->student();

        $this->actingAs($student, 'attendee')
            ->post('/en/me/edit', $this->payload([
                'full_name' => 'Hemin K. Salih',
                'school_name' => 'Sulaimani Preparatory',
            ]))
            ->assertRedirect('/en/me');

        $student->refresh();

        $this->assertSame('Hemin K. Salih', $student->full_name);
        $this->assertSame('Erbil', $student->city);
        $this->assertSame('ku', $student->locale);
        $this->assertSame('Sulaimani Preparatory', $student->school_name);
    }

    /**
     * The number is the badge. Editing it from a signed-in session would be a
     * way to move somebody else's badge onto your own handset.
     */
    public function test_the_phone_number_cannot_be_changed_here(): void
    {
        $student = $this->student();
        $before = $student->phone;

        $this->actingAs($student, 'attendee')
            ->post('/en/me/edit', $this->payload(['phone' => '7700000000', 'phone_country' => '+1']))
            ->assertRedirect('/en/me');

        $student->refresh();

        $this->assertSame($before, $student->phone);
        $this->assertSame('+964', $student->phone_country);
    }

    public function test_an_email_already_in_use_is_refused(): void
    {
        $this->student(['email' => 'taken@example.com', 'phone' => '7719995102']);
        $me = $this->student(['email' => 'mine@example.com', 'phone' => '7719995103']);

        $this->actingAs($me, 'attendee')
            ->post('/en/me/edit', $this->payload(['email' => 'taken@example.com']))
            ->assertSessionHasErrors('email');

        $this->assertSame('mine@example.com', $me->fresh()->email);
    }

    /** Keeping your own address is not a clash with yourself. */
    public function test_keeping_your_own_email_is_not_a_clash(): void
    {
        $me = $this->student(['email' => 'mine@example.com']);

        $this->actingAs($me, 'attendee')
            ->post('/en/me/edit', $this->payload(['email' => 'mine@example.com']))
            ->assertSessionHasNoErrors();
    }

    public function test_a_blank_password_leaves_the_old_one_alone(): void
    {
        $student = $this->student();
        $before = $student->password;

        $this->actingAs($student, 'attendee')
            ->post('/en/me/edit', $this->payload(['password' => '']))
            ->assertRedirect('/en/me');

        $this->assertSame($before, $student->fresh()->password);
        $this->assertTrue(Hash::check('a-good-password', $student->fresh()->password));
    }

    public function test_a_new_password_signs_them_in_next_time(): void
    {
        $student = $this->student();

        $this->actingAs($student, 'attendee')
            ->post('/en/me/edit', $this->payload(['password' => 'a-different-password']))
            ->assertRedirect('/en/me');

        $this->assertTrue(Hash::check('a-different-password', $student->fresh()->password));
    }

    /* --------------------------------------------------------------- photo -- */

    public function test_a_photo_is_stored_and_shown(): void
    {
        Storage::fake('public');
        $student = $this->student();

        $this->actingAs($student, 'attendee')
            ->post('/en/me/edit', $this->payload([
                'photo' => UploadedFile::fake()->image('me.jpg', 600, 600),
            ]))
            ->assertRedirect('/en/me');

        $path = $student->fresh()->photo_path;

        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);

        $this->actingAs($student, 'attendee')->get('/en/me')->assertOk()->assertSee($path, false);
    }

    /** Replacing leaves no orphan on the disk. */
    public function test_replacing_a_photo_removes_the_old_file(): void
    {
        Storage::fake('public');
        $student = $this->student();

        $this->actingAs($student, 'attendee')
            ->post('/en/me/edit', $this->payload(['photo' => UploadedFile::fake()->image('one.jpg', 400, 400)]));
        $first = $student->fresh()->photo_path;

        $this->actingAs($student, 'attendee')
            ->post('/en/me/edit', $this->payload(['photo' => UploadedFile::fake()->image('two.jpg', 400, 400)]));
        $second = $student->fresh()->photo_path;

        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);
    }

    public function test_removing_a_photo_takes_the_file_with_it(): void
    {
        Storage::fake('public');
        $student = $this->student();

        $this->actingAs($student, 'attendee')
            ->post('/en/me/edit', $this->payload(['photo' => UploadedFile::fake()->image('me.jpg', 400, 400)]));
        $path = $student->fresh()->photo_path;

        $this->actingAs($student, 'attendee')
            ->post('/en/me/edit', $this->payload(['remove_photo' => '1']));

        $this->assertNull($student->fresh()->photo_path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_something_that_is_not_an_image_is_refused(): void
    {
        Storage::fake('public');
        $student = $this->student();

        $this->actingAs($student, 'attendee')
            ->post('/en/me/edit', $this->payload([
                'photo' => UploadedFile::fake()->create('cv.pdf', 200, 'application/pdf'),
            ]))
            ->assertSessionHasErrors('photo');

        $this->assertNull($student->fresh()->photo_path);
    }

    public function test_initials_stand_in_until_there_is_a_photo(): void
    {
        $student = $this->student(['full_name' => 'Hemin Karim Salih']);

        // First and last, not first and middle — nobody is known by their middle name.
        $this->assertSame('HS', $student->initials());
        $this->assertSame('L', $this->student(['full_name' => 'Lava', 'phone' => '7719995104'])->initials());
    }
}
