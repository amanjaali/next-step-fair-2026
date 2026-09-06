<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Support\Html;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Rich-text content, sanitised on the server.
 *
 * The dashboard's editors print their HTML unescaped, so whatever an editor
 * account can store is markup in every visitor's browser. TinyMCE's toolbar is
 * not the boundary — it has a source view, and an editor password can be stolen.
 */
class RichTextSanitisationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public static function payloads(): array
    {
        return [
            'script tag' => ['<script>alert(1)</script>', ['alert(1)', '<script']],
            'image error handler' => ['<img src=x onerror=alert(1)>', ['onerror']],
            'javascript url' => ['<a href="javascript:alert(1)">Click</a>', ['javascript:']],
            'entity-escaped javascript url' => ['<a href="java&#09;script:alert(1)">x</a>', ['script:alert']],
            'uppercase scheme' => ['<a href="JaVaScRiPt:alert(1)">x</a>', ['alert(1)']],
            'svg handler' => ['<svg onload=alert(1)></svg>', ['onload', '<svg']],
            'iframe to anywhere' => ['<iframe src="https://evil.example/login"></iframe>', ['evil.example']],
            'inline style fetch' => ['<p style="background:url(javascript:alert(1))">x</p>', ['javascript']],
            'form' => ['<form action="https://evil.example"><input name="p"></form>', ['<form', '<input']],
            'body handler' => ['<body onload=alert(1)>text</body>', ['onload']],
            'object' => ['<object data="evil.swf"></object>', ['<object']],
            'data url image' => ['<img src="data:text/html;base64,PHNjcmlwdD4=">', ['data:text/html']],
        ];
    }

    #[DataProvider('payloads')]
    public function test_the_filter_removes_a_known_attack(string $payload, array $mustNotSurvive): void
    {
        $clean = Html::clean($payload);

        foreach ($mustNotSurvive as $needle) {
            $this->assertStringNotContainsString($needle, $clean, "「{$needle}」 survived: {$clean}");
        }
    }

    /** The filter is worth nothing if it also eats ordinary editing. */
    public function test_ordinary_formatting_survives_untouched(): void
    {
        $written = '<h2>A heading</h2><p>Some <strong>bold</strong> and <em>italic</em> text with a '
            .'<a href="https://nextstepfair.com/en/agenda">link</a>.</p>'
            .'<ul><li>One</li><li>Two</li></ul>'
            .'<blockquote>A quotation</blockquote>'
            .'<table><tbody><tr><th scope="col">Day</th><td colspan="2">28 September</td></tr></tbody></table>'
            .'<p style="text-align:center">Centred</p>'
            .'<img src="/storage/posts/cover.jpg" alt="A photograph" width="800">';

        $clean = Html::clean($written);

        foreach ([
            '<h2>', '<strong>', '<em>', 'href="https://nextstepfair.com/en/agenda"',
            '<ul>', '<li>', '<blockquote>', 'scope="col"', 'colspan="2"',
            'style="text-align:center"', 'src="/storage/posts/cover.jpg"', 'alt="A photograph"',
        ] as $kept) {
            $this->assertStringContainsString($kept, $clean, "the filter ate {$kept}");
        }
    }

    public function test_kurdish_and_arabic_come_back_unharmed(): void
    {
        $clean = Html::clean('<p dir="rtl">خوێندنی بەردەوام لە کوردستان</p><p dir="rtl">التعليم العالي</p>');

        $this->assertStringContainsString('خوێندنی بەردەوام لە کوردستان', $clean);
        $this->assertStringContainsString('التعليم العالي', $clean);
        $this->assertStringContainsString('dir="rtl"', $clean);
    }

    /** A video embed is the one iframe an editor is told to paste. */
    public function test_a_youtube_embed_still_works(): void
    {
        $clean = Html::clean('<iframe src="https://www.youtube.com/embed/abc123" width="560"></iframe>');

        $this->assertStringContainsString('https://www.youtube.com/embed/abc123', $clean);
    }

    public function test_words_inside_a_removed_tag_are_not_lost(): void
    {
        $clean = Html::clean('<p>Before <marquee>the announcement</marquee> after</p>');

        $this->assertStringContainsString('the announcement', $clean);
        $this->assertStringNotContainsString('<marquee', $clean);
    }

    public function test_a_link_opened_in_a_new_tab_cannot_reach_back(): void
    {
        $this->assertStringContainsString(
            'rel="noopener noreferrer"',
            Html::clean('<a href="https://example.com" target="_blank">x</a>')
        );
    }

    /** The proof that matters: the payload in the database, on the live page. */
    public function test_a_payload_stored_in_an_article_does_not_reach_the_browser(): void
    {
        $post = Post::query()->whereNotNull('published_at')->firstOrFail();

        $post->setTranslation('body', 'en',
            '<p>Real copy.</p><script>alert(1)</script><img src=x onerror=alert(1)>'
            .'<a href="javascript:alert(1)">Click</a>'
        );
        $post->save();

        $page = $this->get("/en/news/{$post->slug}")->assertOk()->getContent();

        $this->assertStringContainsString('Real copy.', $page);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $page);
        $this->assertStringNotContainsString('onerror=alert(1)', $page);
        $this->assertStringNotContainsString('javascript:alert(1)', $page);
    }

    /**
     * The structured-data block is a script tag with editor text inside it, so a
     * title containing </script> would close it and start writing markup.
     */
    public function test_a_headline_cannot_break_out_of_the_structured_data(): void
    {
        $post = Post::query()->whereNotNull('published_at')->firstOrFail();

        $post->setTranslation('title', 'en', 'Admissions </script><script>alert(1)</script> report');
        $post->save();

        $page = $this->get("/en/news/{$post->slug}")->assertOk()->getContent();

        $this->assertStringNotContainsString('</script><script>alert(1)', $page);
    }
}
