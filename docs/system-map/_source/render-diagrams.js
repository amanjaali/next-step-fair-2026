/**
 * Renders every mermaid block in map-body.html to a standalone SVG file.
 *
 * The documentation site then carries no JavaScript at all: the diagrams are laid
 * out once, here, and shipped as finished drawings. That also removes the font
 * race for good — nothing is measured in the reader's browser.
 */
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const OUT = path.join(__dirname, 'svg');
fs.mkdirSync(OUT, { recursive: true });

const html = fs.readFileSync(path.join(__dirname, 'map.html'), 'utf8');
const blocks = [...html.matchAll(/<pre class="mermaid">([\s\S]*?)<\/pre>/g)].map((m) =>
  m[1].replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>')
);

(async () => {
  const b = await chromium.launch({ executablePath: '/opt/pw-browsers/chromium-1194/chrome-linux/chrome' });
  const p = await b.newPage();
  await p.setContent('<!doctype html><body style="font-family:ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif"><div id="x"></div>');
  await p.addScriptTag({ path: path.join(__dirname, 'node_modules/mermaid/dist/mermaid.min.js') });
  await p.evaluate(() => window.mermaid.initialize({ startOnLoad: false, securityLevel: 'loose' }));

  for (let i = 0; i < blocks.length; i++) {
    const n = String(i + 1).padStart(2, '0');
    const res = await p.evaluate(async ([src, id]) => {
      const { svg } = await window.mermaid.render('d' + id, src);
      return svg;
    }, [blocks[i], n]);

    // Strip the id-scoped max-width mermaid injects, and pin the font inside the
    // drawing so it does not depend on whatever the page around it inherits.
    let svg = res
      .replace(/<style>/, '<style>svg{font-family:ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,Helvetica,Arial,sans-serif}')
      .replace(/style="max-width:[^"]*"/, '');

    // Curve coordinates come out at 17 significant figures, which on the busier
    // diagrams is 200kb of noise nobody can see. Two decimals is well below one
    // device pixel at any sane zoom.
    svg = svg.replace(/\s(d|points|transform)="([^"]*)"/g, (all, attr, value) =>
      ` ${attr}="${value.replace(/-?\d+\.\d+/g, (num) => String(Math.round(parseFloat(num) * 100) / 100))}"`
    );

    fs.writeFileSync(path.join(OUT, `fig-${n}.svg`), svg);
    const m = svg.match(/viewBox="([^"]+)"/);
    const dims = m ? m[1].split(/[\s,]+/).slice(2).map((v) => Math.round(parseFloat(v))) : ['?', '?'];
    console.log(`fig-${n}.svg  ${dims[0]} x ${dims[1]}  ${Math.round(svg.length / 1024)}kb`);
  }

  await b.close();
})();
