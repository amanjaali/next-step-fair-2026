<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The registration wizard is client-side, so these guard the contract the
 * JavaScript depends on: every field the server requires must be marked required
 * in the markup, and every one of those must have somewhere to show its message.
 *
 * Without both, a visitor presses the button and nothing happens.
 */
class RegistrationMarkupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public static function forms(): array
    {
        return [
            'fair student' => ['/en/register/fair'],
            'fair parent' => ['/en/register/fair?type=parent'],
            'conference government' => ['/en/register/conference'],
            'conference official' => ['/en/register/conference?type=official'],
        ];
    }

    #[DataProvider('forms')]
    public function test_every_required_field_can_show_an_error(string $url): void
    {
        $html = $this->get($url)->assertOk()->getContent();

        preg_match_all('/data-required="([a-z_]+)"/', $html, $required);
        preg_match_all('/data-error-for="([a-z_]+)"/', $html, $slots);

        $missing = array_diff(array_unique($required[1]), array_unique($slots[1]));

        $this->assertSame([], array_values($missing), sprintf(
            'These fields are required but have no [data-error-for] to render the message in: %s',
            implode(', ', $missing)
        ));
    }

    #[DataProvider('forms')]
    public function test_the_wizard_never_reaches_for_dollar_el(string $url): void
    {
        // Alpine rebinds $el per expression, so a method called from a button sees
        // the button — which silently broke submit() and every panel lookup.
        $this->get($url)->assertOk()->assertDontSee('$el.submit', false);
    }
}
