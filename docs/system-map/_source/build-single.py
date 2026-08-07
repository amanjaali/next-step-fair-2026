#!/usr/bin/env python3
"""
Builds the whole map as one HTML file.

Same content as the seven-page site, in a single document with the fonts, the
styles, the diagrams and the search index all inside it. Nothing to unzip and no
folder to keep together — it can be emailed, put on a USB stick, or opened from
a downloads folder years from now and still work.

    python3 build-single.py
"""
import json
import os
import re

import build as site

OUT = os.path.join(os.path.dirname(site.HERE), 'next-step-fair-2026-system-map.html')

# One page, so every link is an anchor and the groups are just headings.
GROUPS = [(label, sids) for _, label, _, sids in site.PAGES]


def sidebar():
    out = ['<nav class="nav" aria-label="Contents">']
    for label, sids in GROUPS:
        out.append('<div class="nav-group">')
        out.append(f'<span class="nav-page">{label}</span><ul>')
        for sid in sids:
            out.append(
                f'<li><a href="#{sid}"><span class="n">{site.NUMBER[sid]}</span>'
                f'<span>{site.TITLE[sid]}</span></a></li>'
            )
        out.append('</ul></div>')
    out.append('</nav>')
    return '\n'.join(out)


def build():
    body, index = [site.masthead, site.HOW_TO_READ], []

    for sid in site.ORDER:
        content = site.SECTION[sid]
        # Cross-references are plain anchors here, not links to another page.
        content = re.sub(
            r'see section (\d+)',
            lambda m: f'see <a class="link" href="#{site.BY_NUMBER[m.group(1)]}">section {m.group(1)}</a>'
            if m.group(1) in site.BY_NUMBER else m.group(0),
            content,
        )
        body.append(f'<section id="{sid}">{site.inline_diagrams(content)}</section>')

        index.append(site.index_entry(sid, ''))

    page = f"""<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Next Step Fair 2026 — System Map</title>
<meta name="description" content="Every page, flow and data path in the Next Step Fair 2026 platform.">
<style>
{site.FONTS}
{site.style}
{site.EXTRA_CSS}
/* Single file: the sidebar is a table of contents, not navigation between pages. */
.nav-page {{ cursor: default; }}
</style>
</head>
<body>
<a class="skip" href="#main">Skip to content</a>
<div class="shell">
<aside class="rail">
    <div class="rail-top">
        <a class="rail-mark" href="#main">
            <span class="rail-name">Next Step Fair 2026</span>
            <span class="rail-sub">System documentation</span>
        </a>
        <button type="button" class="theme" id="theme" aria-label="Switch between light and dark">
            <span data-when="light">Dark</span><span data-when="dark">Light</span>
        </button>
    </div>
    <form class="find" role="search" onsubmit="return false">
        <label class="sr-only" for="q">Search this document</label>
        <input id="q" type="search" placeholder="Search — try badge, OTP, matching" autocomplete="off">
        <ul id="results" hidden></ul>
    </form>
    {sidebar()}
</aside>
<main id="main">
{chr(10).join(body)}
<footer>
    Drawn from the code as it stands: routes, controllers, services and migrations.
    Where a diagram and the code disagree, the code is right and the diagram is a bug.
    <br>This is a single self-contained file — no server, no internet, nothing else needed.
    Print it to PDF from the browser if you need it on paper.
</footer>
</main>
</div>
<script>
const INDEX = {json.dumps(index, ensure_ascii=False)};
{site.JS}
</script>
</body>
</html>
"""

    open(OUT, 'w', encoding='utf-8').write(page)
    print(f'{os.path.basename(OUT)}  {os.path.getsize(OUT) // 1024} kb  '
          f'{len(site.ORDER)} sections, {page.count("</svg>")} diagrams')


if __name__ == '__main__':
    build()
