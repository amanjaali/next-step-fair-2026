<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

/**
 * Self-hosted TinyMCE, used for every long-form text area in the admin.
 *
 * No CDN and no API key: the editor is served from /vendor/tinymce, which is
 * copied out of node_modules at build time. `direction` follows the language tab
 * being edited, so Kurdish and Arabic are composed right-to-left.
 */
class TinyEditor extends Field
{
    protected string $view = 'filament.forms.components.tiny-editor';

    protected string $editorHeight = '420px';

    protected string $textDirection = 'ltr';

    protected bool $minimal = false;

    public function height(string $height): static
    {
        $this->editorHeight = $height;

        return $this;
    }

    public function direction(string $direction): static
    {
        $this->textDirection = $direction;

        return $this;
    }

    /** A cut-down toolbar for short fields such as a speaker biography. */
    public function minimal(bool $minimal = true): static
    {
        $this->minimal = $minimal;

        return $this;
    }

    public function getEditorHeight(): string
    {
        return $this->editorHeight;
    }

    public function getTextDirection(): string
    {
        return $this->textDirection;
    }

    public function isMinimal(): bool
    {
        return $this->minimal;
    }

    public function getPlugins(): string
    {
        return $this->minimal
            ? 'lists link autolink paste'
            : 'advlist autolink lists link image media table code fullscreen searchreplace visualblocks wordcount directionality anchor charmap preview';
    }

    public function getToolbar(): string
    {
        return $this->minimal
            ? 'bold italic | bullist numlist | link | removeformat'
            : 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist outdent indent | ltr rtl | link image media table blockquote | removeformat code fullscreen';
    }
}
