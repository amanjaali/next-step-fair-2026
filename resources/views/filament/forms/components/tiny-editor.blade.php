@php
    $statePath = $getStatePath();
    $editorId = 'tiny-'.str()->random(8);
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        wire:ignore
        x-data="{
            editor: null,
            state: $wire.$entangle('{{ $statePath }}'),
            init() {
                const boot = () => {
                    window.tinymce.init({
                        target: this.$refs.editor,
                        license_key: 'gpl',
                        base_url: '{{ asset('vendor/tinymce') }}',
                        suffix: '.min',
                        height: '{{ $getEditorHeight() }}',
                        menubar: false,
                        branding: false,
                        promotion: false,
                        directionality: '{{ $getTextDirection() }}',
                        plugins: '{{ $getPlugins() }}',
                        toolbar: '{{ $getToolbar() }}',
                        // Editor content is styled to match the published article.
                        content_style: `
                            body { font-family: Manrope, system-ui, sans-serif; font-size: 16px; line-height: 1.75; color: #1A1C1D; }
                            body[dir='rtl'] { font-family: 'Noto Sans Arabic', Manrope, sans-serif; line-height: 1.9; }
                            h2 { font-family: 'Space Grotesk', sans-serif; font-size: 26px; }
                            h3 { font-family: 'Space Grotesk', sans-serif; font-size: 21px; }
                            blockquote { border-inline-start: 6px solid #B64698; padding-inline-start: 18px; margin-inline-start: 0; }
                            a { color: #2C4BE0; }
                        `,
                        setup: (editor) => {
                            this.editor = editor;
                            editor.on('init', () => editor.setContent(this.state ?? ''));
                            editor.on('change keyup undo redo', () => { this.state = editor.getContent(); });
                        },
                    });
                };

                if (window.tinymce) {
                    boot();
                } else {
                    const script = document.createElement('script');
                    script.src = '{{ asset('vendor/tinymce/tinymce.min.js') }}';
                    script.referrerPolicy = 'origin';
                    script.onload = boot;
                    document.head.appendChild(script);
                }

                // Keep the editor in step when Livewire replaces the state (e.g. after save).
                this.$watch('state', (value) => {
                    if (this.editor && !this.editor.hasFocus() && value !== this.editor.getContent()) {
                        this.editor.setContent(value ?? '');
                    }
                });
            },
            destroy() {
                this.editor?.remove();
            },
        }"
        class="fi-fo-rich-editor overflow-hidden rounded-lg ring-1 ring-gray-950/10 dark:ring-white/20"
    >
        <textarea x-ref="editor" id="{{ $editorId }}"></textarea>
    </div>
</x-dynamic-component>
