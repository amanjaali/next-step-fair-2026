/**
 * Renders the share cards to PNG.
 *
 * The cards people post have to be real images — Instagram will not take a web
 * page, and a screenshot somebody takes themselves is the wrong size and picks
 * up the browser chrome. So the artwork is designed as a page (see
 * `resources/views/share/card.blade.php`, reachable at /{lang}/share/card/…)
 * and baked here into `public/assets/share/`, once, by whoever builds a release.
 *
 * Doing it here rather than on the server also settles the hard part for good:
 * Kurdish and Arabic need contextual shaping and bidi, which a browser does
 * correctly and PHP's image libraries do not.
 *
 *   php artisan serve &
 *   node tools/build-share-cards.cjs [baseUrl]
 */
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const BASE = process.argv[2] || 'http://127.0.0.1:8000';
const OUT = path.join(__dirname, '..', 'public', 'assets', 'share');

const LOCALES = ['en', 'ku', 'ar'];
const VARIANTS = ['student', 'parent', 'delegate'];
// feed and story are what a person posts. `og` is never chosen by anybody —
// it is what LinkedIn and Facebook fetch when they unfurl a shared link.
const FORMATS = { feed: [1080, 1080], story: [1080, 1920], og: [1200, 630] };

const written = [];

(async () => {
  fs.mkdirSync(OUT, { recursive: true });

  const browser = await chromium.launch({
    executablePath: process.env.CHROMIUM_PATH || undefined,
  });

  console.log(`Reading the cards from ${BASE}`);

  for (const locale of LOCALES) {
    for (const variant of VARIANTS) {
      for (const [format, [width, height]] of Object.entries(FORMATS)) {
        const page = await browser.newPage({
          viewport: { width, height },
          deviceScaleFactor: 1,
        });

        const url = `${BASE}/${locale}/share/card/${variant}/${format}`;

        /*
         * 'load', not 'networkidle'. Idle means half a second with no request
         * in flight, and one request that never finishes — a webfont host the
         * machine cannot reach, a browser extension, a hanging analytics beacon
         * — means it never arrives and the whole build dies on a timeout with
         * nothing to show for it. The page's own load event is a fact about the
         * page; idle is a guess about the network.
         */
        const response = await page.goto(url, { waitUntil: 'load', timeout: 60000 });

        if (!response || !response.ok()) {
          throw new Error(`${url} returned ${response ? response.status() : 'nothing'}`);
        }

        // Webfonts decide the line breaks, so nothing is measured until they
        // have loaded — or until it is clear they are not going to. A card set
        // in the fallback face is worth more than no card at all.
        await Promise.race([
          page.evaluate(() => document.fonts.ready),
          page.waitForTimeout(8000),
        ]);

        // And the marks, which are the whole reason for a rebuild.
        await page.evaluate(() => Promise.all(
          [...document.images].map((image) => image.complete
            ? null
            : new Promise((resolve) => { image.onload = image.onerror = resolve; }))
        ));

        await page.waitForTimeout(150);

        const file = path.join(OUT, `${locale}-${variant}-${format}.png`);
        await page.screenshot({ path: file });
        await page.close();

        const kb = Math.round(fs.statSync(file).size / 1024);
        console.log(`${path.basename(file).padEnd(28)} ${width}×${height}  ${kb}kb`);
        written.push(file);
      }
    }
  }

  await browser.close();

  // Screenshots come out as full 24-bit PNGs — a quarter of a megabyte each, for
  // artwork that is flat colour, one gradient and some type. A 256-colour palette
  // takes about two thirds off with nothing visible lost, which matters twice:
  // the page shows two of these, and the whole point is that somebody on a phone
  // downloads one.
  const shrink = path.join(__dirname, 'shrink-pngs.py');

  if (fs.existsSync(shrink)) {
    const { spawnSync } = require('child_process');
    const before = written.reduce((n, f) => n + fs.statSync(f).size, 0);
    const result = spawnSync('python3', [shrink, ...written], { stdio: 'inherit' });

    if (result.status === 0) {
      const after = written.reduce((n, f) => n + fs.statSync(f).size, 0);
      console.log(`\npalette pass: ${Math.round(before / 1024)}kb -> ${Math.round(after / 1024)}kb`);
    } else {
      console.log('\nSkipped the palette pass (needs python3 with Pillow). The cards are fine, just larger.');
    }
  }
})();
