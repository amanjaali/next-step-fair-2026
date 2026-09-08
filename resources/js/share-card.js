/**
 * Puts the person's name on their share card, in the browser.
 *
 * The artwork is pre-rendered and identical for everybody; the name is the one
 * thing that makes it theirs, and it is drawn here rather than baked in on the
 * server for three reasons. There are as many names as there are people. A
 * Kurdish or Arabic name needs contextual shaping and bidi, which a browser does
 * correctly and PHP's image libraries do not. And nothing personal is written to
 * disk anywhere.
 *
 * If any of it fails — no canvas, a font that never loads, an image the browser
 * will not let us read back — the download link is left exactly as the server
 * rendered it: the plain card, which is still worth posting.
 */
export default function shareCard() {
    const draw = async (figure) => {
        const canvas = figure.querySelector('canvas');
        const link = figure.querySelector('[data-share-download]');
        const source = figure.dataset.card;
        const name = (figure.dataset.name || '').trim();

        if (!canvas || !source) {
            return;
        }

        const slot = {
            width: Number(figure.dataset.width),
            height: Number(figure.dataset.height),
            x: Number(figure.dataset.x),
            y: Number(figure.dataset.y),
            size: Number(figure.dataset.size),
            max: Number(figure.dataset.max),
        };

        const image = new Image();
        image.decoding = 'async';

        await new Promise((resolve, reject) => {
            image.onload = resolve;
            image.onerror = reject;
            image.src = source;
        });

        canvas.width = slot.width;
        canvas.height = slot.height;

        const ctx = canvas.getContext('2d');
        ctx.drawImage(image, 0, 0, slot.width, slot.height);

        if (name) {
            // The card is right-to-left in Kurdish and Arabic, so the name sits
            // in the corner opposite the handle either way.
            const rtl = document.documentElement.dir === 'rtl';

            // Wait for the webfont, or the first paint measures a fallback face
            // and the name comes out at the wrong width.
            if (document.fonts && document.fonts.ready) {
                await document.fonts.ready;
            }

            let size = slot.size;
            ctx.textBaseline = 'alphabetic';
            ctx.direction = rtl ? 'rtl' : 'ltr';
            ctx.textAlign = rtl ? 'left' : 'right';

            // A long name shrinks rather than running off the card or over the
            // wordmark. Nobody's name gets cut in half.
            do {
                ctx.font = `700 ${size}px "Manrope", ui-sans-serif, system-ui, sans-serif`;
                size -= 2;
            } while (ctx.measureText(name).width > slot.max && size > 18);

            ctx.fillStyle = 'rgba(255,255,255,0.92)';
            ctx.fillText(name, rtl ? slot.x : slot.width - slot.x, slot.y);
        }

        canvas.classList.remove('hidden');
        figure.querySelector('img')?.classList.add('hidden');

        if (link) {
            canvas.toBlob((blob) => {
                if (!blob) {
                    return;
                }

                link.href = URL.createObjectURL(blob);
                link.download = figure.dataset.filename || 'next-step-2026.png';
            }, 'image/png');
        }
    };

    document.querySelectorAll('[data-share-figure]').forEach((figure) => {
        draw(figure).catch(() => {
            // Leave the plain server-rendered card and its download link alone.
        });
    });
}
