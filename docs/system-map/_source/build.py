#!/usr/bin/env python3
"""
Builds the Next Step Fair documentation site from the single-page map.

The diagrams are already rendered to SVG, so the output is plain HTML and CSS
with one small script for search and the theme toggle. It opens from the file
system — no server, no build step, no network.
"""
import html
import os
import re
import shutil

HERE = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.dirname(HERE)
ASSETS = os.path.join(OUT, 'assets')

SRC = open(os.path.join(HERE, 'map.html'), encoding='utf-8').read()
FONTS = open(os.path.join(HERE, 'fonts.css'), encoding='utf-8').read()

# ------------------------------------------------------------------ source --

style = re.search(r'<style>(.*?)</style>', SRC, re.S).group(1).replace('<!--FONTS-->', '')


def strip_rules(css, matches):
    """
    Drops every rule whose selector satisfies `matches`, at the top level and
    inside media queries alike.

    The single-page map styled its index with `.rail a`, which in this shell also
    matches the brand link and the theme button — and beats a plain `.rail-mark`
    on specificity. Deleting the superseded rules is clearer than piling
    overrides on top of them.
    """
    out, i, n = [], 0, len(css)
    while i < n:
        brace = css.find('{', i)
        if brace == -1:
            out.append(css[i:])
            break

        selector = css[i:brace]
        depth, j = 1, brace + 1
        while j < n and depth:
            if css[j] == '{':
                depth += 1
            elif css[j] == '}':
                depth -= 1
            j += 1

        if selector.strip().startswith('@'):
            inner = strip_rules(css[brace + 1:j - 1], matches)
            out.append(selector + '{' + inner + '}')
        elif not matches(selector):
            out.append(css[i:j])
        i = j

    return ''.join(out)


# The sidebar is rebuilt from scratch below; nothing from the old one survives.
style = strip_rules(style, lambda s: '.rail' in s)
masthead = re.search(r'<header class="masthead">.*?</header>', SRC, re.S).group(0)
sections = re.findall(r'<section id="([^"]+)">(.*?)</section>', SRC, re.S)
SECTION = {sid: body for sid, body in sections}
ORDER = [sid for sid, _ in sections]

# Diagrams appear once each, in this order, and were rendered in the same pass.
FIGS = iter(range(1, 99))


def inline_diagrams(body):
    def swap(_):
        n = f'{next(FIGS):02d}'
        svg = open(os.path.join(HERE, 'svg', f'fig-{n}.svg'), encoding='utf-8').read()
        svg = re.sub(r'^<svg ', '<svg role="img" ', svg)
        return svg
    return re.sub(r'<pre class="mermaid">.*?</pre>', swap, body, flags=re.S)


# ------------------------------------------------------------------- pages --

PAGES = [
    ('index.html', 'Start here', 'Ground', ['glance', 'urls']),
    ('registration.html', 'Getting a badge', 'Three doors to a badge', ['doors', 'fair', 'upgrade', 'duplicate', 'conference']),
    ('attendees.html', 'After registering', 'The attendee side', ['account', 'opportunities', 'scholarship', 'matching']),
    ('exhibitors.html', 'Exhibitors', 'The other side of the platform', ['portal', 'leads']),
    ('operations.html', 'Event days', 'Doors open', ['gate', 'messaging', 'qr']),
    ('internals.html', 'Behind it', 'Dashboard, data and schedule', ['admin', 'data', 'calendar', 'guards']),
    ('routes.html', 'Every URL', 'The complete reference', ['routes']),
]

PAGE_OF = {sid: page for page, _, _, sids in PAGES for sid in sids}
NUMBER = {sid: re.search(r'<span class="sec-n">(\d+)</span>', SECTION[sid]).group(1) for sid in ORDER}
TITLE = {sid: re.search(r'<h2>(.*?)</h2>', SECTION[sid], re.S).group(1).strip() for sid in ORDER}
BY_NUMBER = {NUMBER[sid]: sid for sid in ORDER}


def link_to(sid):
    return f'{PAGE_OF[sid]}#{sid}'


def resolve_cross_references(body):
    """"see section 06" becomes a link now that sections live on separate pages."""
    def swap(m):
        sid = BY_NUMBER.get(m.group(1))
        if not sid:
            return m.group(0)
        return f'see <a class="link" href="{link_to(sid)}">section {m.group(1)}</a>'
    return re.sub(r'see section (\d+)', swap, body)


def index_entry(sid, page):
    """
    One searchable record per section.

    The id is folded into the text because it carries words the prose does not:
    somebody looking for "duplicate" or "upgrade" is thinking in those terms even
    though the section is titled "One number, one badge".
    """
    text = re.sub(r'<[^>]+>', ' ', SECTION[sid])
    text = html.unescape(re.sub(r'\s+', ' ', text))

    return {'p': page, 'a': sid, 'n': NUMBER[sid], 't': TITLE[sid],
            'x': (sid + ' ' + text)[:1400]}


def sidebar(current_page):
    out = ['<nav class="nav" aria-label="Documentation">']
    for page, label, _, sids in PAGES:
        here = page == current_page
        out.append(f'<div class="nav-group{" is-here" if here else ""}">')
        out.append(f'<a class="nav-page" href="{page}"{" aria-current=page" if here else ""}>{label}</a>')
        out.append('<ul>')
        for sid in sids:
            href = f'#{sid}' if here else f'{page}#{sid}'
            out.append(f'<li><a href="{href}"><span class="n">{NUMBER[sid]}</span><span>{TITLE[sid]}</span></a></li>')
        out.append('</ul></div>')
    out.append('</nav>')
    return '\n'.join(out)


def pager(index):
    parts = []
    if index > 0:
        p, label, _, _ = PAGES[index - 1]
        parts.append(f'<a class="pager-prev" href="{p}"><span>Previous</span><strong>{label}</strong></a>')
    else:
        parts.append('<span></span>')
    if index < len(PAGES) - 1:
        p, label, _, _ = PAGES[index + 1]
        parts.append(f'<a class="pager-next" href="{p}"><span>Next</span><strong>{label}</strong></a>')
    return f'<nav class="pager">{"".join(parts)}</nav>'


SHELL = """<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{title} — Next Step Fair 2026 documentation</title>
<meta name="description" content="{descr}">
<link rel="stylesheet" href="assets/fonts.css">
<link rel="stylesheet" href="assets/docs.css">
</head>
<body>
<a class="skip" href="#main">Skip to content</a>
<div class="shell">
<aside class="rail">
    <div class="rail-top">
        <a class="rail-mark" href="index.html">
            <span class="rail-name">Next Step Fair 2026</span>
            <span class="rail-sub">System documentation</span>
        </a>
        <button type="button" class="theme" id="theme" aria-label="Switch between light and dark">
            <span data-when="light">Dark</span><span data-when="dark">Light</span>
        </button>
    </div>
    <form class="find" role="search" onsubmit="return false">
        <label class="sr-only" for="q">Search the documentation</label>
        <input id="q" type="search" placeholder="Search — try badge, OTP, matching" autocomplete="off">
        <ul id="results" hidden></ul>
    </form>
    {sidebar}
</aside>
<main id="main">
{crumb}
{content}
{pager}
<footer>
    Drawn from the code as it stands: routes, controllers, services and migrations.
    Where a diagram and the code disagree, the code is right and the diagram is a bug.
    <br>Rebuild the diagrams after changing a flow, or the map starts lying.
</footer>
</main>
</div>
<script src="assets/docs.js" defer></script>
</body>
</html>
"""


def build():
    os.makedirs(ASSETS, exist_ok=True)

    search_index = []

    for i, (page, label, descr, sids) in enumerate(PAGES):
        body = []
        if page == 'index.html':
            body.append(masthead)
            body.append(HOW_TO_READ)
        for sid in sids:
            content = resolve_cross_references(inline_diagrams(SECTION[sid]))
            body.append(f'<section id="{sid}">{content}</section>')

            search_index.append(index_entry(sid, page))

        crumb = ('' if page == 'index.html' else
                 f'<p class="crumb"><a href="index.html">Documentation</a> <span>/</span> {label}</p>')

        open(os.path.join(OUT, page), 'w', encoding='utf-8').write(SHELL.format(
            title=label,
            descr=html.escape(descr, quote=True),
            sidebar=sidebar(page),
            crumb=crumb,
            content='\n'.join(body),
            pager=pager(i),
        ))

    open(os.path.join(ASSETS, 'fonts.css'), 'w', encoding='utf-8').write(FONTS)
    open(os.path.join(ASSETS, 'docs.css'), 'w', encoding='utf-8').write(style + EXTRA_CSS)

    import json
    open(os.path.join(ASSETS, 'docs.js'), 'w', encoding='utf-8').write(
        'const INDEX = ' + json.dumps(search_index, ensure_ascii=False) + ';\n' + JS
    )

    print(f'{len(PAGES)} pages, {len(search_index)} sections, {sum(1 for _ in os.listdir(os.path.join(HERE, "svg")))} diagrams')
    for f in sorted(os.listdir(OUT)):
        p = os.path.join(OUT, f)
        if os.path.isfile(p):
            print(f'  {f:22} {os.path.getsize(p) // 1024:>5} kb')


HOW_TO_READ = """
<section id="how">
    <div class="sec-head"><span class="sec-n">00</span><h2>How to read this</h2></div>
    <p>
        Seven pages, nineteen sections, one diagram per flow. Each diagram is followed
        by the files it lives in, so a picture can be traced to the code that makes it
        true. Colour means the same thing throughout.
    </p>
    <div class="legend">
        <span class="track f">Fair track — students, parents, visitors</span>
        <span class="track c">Conference track — government and officials</span>
        <span class="track i">Exhibitors — universities and institutes</span>
        <span class="track o">Operations — staff, gate, scheduled jobs</span>
    </div>
    <div class="callout">
        <span class="lbl">Reading a diagram</span>
        <p>
            A rounded box is a person. A rectangle is a page or a step. A diamond is a
            decision. A cylinder is something written to the database. Wide diagrams
            scroll sideways inside their own frame — the page itself never does.
        </p>
    </div>
</section>
"""

EXTRA_CSS = """

/* ------------------------------------------------- documentation site -- */

.rail {
    position: sticky;
    top: 0;
    align-self: start;
    max-height: 100vh;
    overflow-y: auto;
    padding: 30px 0 40px;
}

.skip {
    position: absolute;
    left: -9999px;
}

.skip:focus {
    left: 12px;
    top: 12px;
    z-index: 20;
    position: fixed;
    background: var(--raised);
    border: 1px solid var(--edge-firm);
    padding: 10px 16px;
    font-family: var(--display);
    font-size: 13px;
    text-decoration: none;
    color: var(--text);
}

.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    overflow: hidden;
    clip: rect(0 0 0 0);
    clip-path: inset(50%);
    white-space: nowrap;
}

.rail-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 16px;
}

.rail-mark { text-decoration: none; color: inherit; display: block; flex: 1 1 auto; min-width: 0; }
.rail-name { display: block; font-family: var(--display); font-weight: 700; font-size: 14.5px; line-height: 1.25; letter-spacing: -0.012em; }
.rail-sub { display: block; font-size: 11.5px; color: var(--text-faint); margin-top: 3px; }
.rail-mark:hover .rail-name { color: var(--fair); }

.theme {
    font-family: var(--display);
    font-size: 10px;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--text-soft);
    background: transparent;
    border: 1px solid var(--edge-firm);
    padding: 5px 9px;
    cursor: pointer;
    flex: 0 0 auto;
}

.theme:hover { color: var(--text); border-color: var(--fair); }
.theme [data-when='dark'] { display: none; }
:root[data-theme='dark'] .theme [data-when='dark'] { display: inline; }
:root[data-theme='dark'] .theme [data-when='light'] { display: none; }

@media (prefers-color-scheme: dark) {
    :root:not([data-theme='light']) .theme [data-when='dark'] { display: inline; }
    :root:not([data-theme='light']) .theme [data-when='light'] { display: none; }
}

.find { position: relative; margin-bottom: 22px; }

.find input {
    width: 100%;
    font-family: var(--body);
    font-size: 13px;
    padding: 8px 11px;
    background: var(--raised);
    color: var(--text);
    border: 1px solid var(--edge-firm);
}

.find input::placeholder { color: var(--text-faint); }

#results {
    list-style: none;
    margin: 4px 0 0;
    padding: 0;
    position: absolute;
    left: 0;
    right: 0;
    z-index: 10;
    background: var(--raised);
    border: 1px solid var(--edge-firm);
    max-height: 320px;
    overflow-y: auto;
}

#results li { border-bottom: 1px solid var(--edge); }
#results li:last-child { border-bottom: 0; }

#results a {
    display: block;
    padding: 8px 11px;
    text-decoration: none;
    color: var(--text);
    font-size: 13px;
    line-height: 1.35;
}

#results a:hover, #results a:focus { background: var(--ground); }
#results .where { display: block; font-size: 11px; color: var(--text-faint); margin-top: 2px; }
#results .none { padding: 10px 11px; font-size: 13px; color: var(--text-faint); }

.nav { display: block; }
.nav-group { margin-bottom: 16px; }

.nav-page {
    display: block;
    font-family: var(--display);
    font-size: 10px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--text-faint);
    text-decoration: none;
    padding: 0 0 6px 12px;
}

.nav-group.is-here .nav-page { color: var(--fair); }
.nav-page:hover { color: var(--text); }

.nav ul { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 1px; }

.nav li a {
    display: flex;
    gap: 10px;
    align-items: baseline;
    text-decoration: none;
    color: var(--text-soft);
    font-size: 13.5px;
    line-height: 1.35;
    padding: 5px 8px 5px 12px;
    border-left: 2px solid transparent;
}

.nav li a:hover, .nav li a:focus-visible { color: var(--text); border-left-color: var(--fair); }
.nav li a.is-current { color: var(--text); border-left-color: var(--fair); }

.nav li .n {
    font-family: var(--mono);
    font-size: 10.5px;
    color: var(--text-faint);
    font-variant-numeric: tabular-nums;
    flex: 0 0 auto;
}

.crumb {
    font-family: var(--display);
    font-size: 10.5px;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: var(--text-faint);
    margin: 0 0 4px;
}

.crumb a { color: inherit; text-decoration: none; }
.crumb a:hover { color: var(--fair); }
.crumb span { padding: 0 4px; }

/* The rendered diagram, in place of the runtime that used to draw it. */
.sheet-body svg {
    display: block;
    margin: 0 auto;
    height: auto;
    max-width: none;
}

.pager {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    margin-top: 70px;
    padding-top: 24px;
    border-top: 1px solid var(--edge);
}

.pager a {
    text-decoration: none;
    color: var(--text);
    display: block;
    padding: 12px 18px;
    border: 1px solid var(--edge);
    background: var(--raised);
    min-width: 190px;
}

.pager a:hover { border-color: var(--fair); }
.pager-next { text-align: right; }

.pager span {
    display: block;
    font-family: var(--display);
    font-size: 10px;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: var(--text-faint);
    margin-bottom: 3px;
}

.pager strong { font-family: var(--display); font-weight: 500; font-size: 15px; }

@media (max-width: 960px) {
    .rail { padding: 14px 0; }
    .rail-top, .find { display: flex; }
    .rail-name, .rail-sub { display: block; }
    /* One scrolling row of pages. The sections within a page are a scroll away
       on a phone, and a second tier of chips would cost half the screen. */
    .nav { display: flex; flex-direction: row; gap: 0; overflow-x: auto; padding-bottom: 4px; }
    .nav-group { margin: 0 20px 0 0; flex: 0 0 auto; }
    .nav-group ul { display: none; }
    .nav-page { padding: 4px 0; white-space: nowrap; font-size: 11px; }
    .nav-group.is-here .nav-page { border-bottom: 2px solid var(--fair); }
    .pager { flex-direction: column; }
    .pager a { min-width: 0; }
    .pager-next { text-align: left; }
}

@media print {
    .rail, .pager, .skip { display: none; }
    .shell { display: block; max-width: none; padding: 0; }
    figure { break-inside: avoid; box-shadow: none; }
    section { break-before: page; }
}
"""

JS = """
/* Search, the theme toggle, and highlighting the section being read. Everything
   here is an enhancement — with scripting off the site is still complete. */
(function () {
    'use strict';

    var root = document.documentElement;

    /* ---------------------------------------------------------- theme -- */
    var stored = null;
    try { stored = localStorage.getItem('ns-docs-theme'); } catch (e) {}
    if (stored) root.setAttribute('data-theme', stored);

    var button = document.getElementById('theme');
    if (button) {
        button.addEventListener('click', function () {
            var dark = root.getAttribute('data-theme') === 'dark' ||
                (!root.getAttribute('data-theme') &&
                 window.matchMedia('(prefers-color-scheme: dark)').matches);
            var next = dark ? 'light' : 'dark';
            root.setAttribute('data-theme', next);
            try { localStorage.setItem('ns-docs-theme', next); } catch (e) {}
        });
    }

    /* --------------------------------------------------------- search -- */
    var box = document.getElementById('q');
    var list = document.getElementById('results');

    function score(entry, terms) {
        var title = entry.t.toLowerCase();
        var text = entry.x.toLowerCase();
        var total = 0;
        for (var i = 0; i < terms.length; i++) {
            var term = terms[i];
            if (title.indexOf(term) > -1) total += 12;
            var at = text.indexOf(term);
            if (at > -1) total += 3;
            else if (title.indexOf(term) === -1) return 0;
        }
        return total;
    }

    function render(matches, query) {
        list.innerHTML = '';
        if (!matches.length) {
            list.innerHTML = '<li class="none">Nothing matches ' + query + '</li>';
            list.hidden = false;
            return;
        }
        matches.slice(0, 8).forEach(function (entry) {
            var li = document.createElement('li');
            var a = document.createElement('a');
            a.href = entry.p + '#' + entry.a;
            a.innerHTML = entry.t + '<span class="where">Section ' + entry.n + '</span>';
            li.appendChild(a);
            list.appendChild(li);
        });
        list.hidden = false;
    }

    if (box && list) {
        box.addEventListener('input', function () {
            var query = box.value.trim().toLowerCase();
            if (query.length < 2) { list.hidden = true; return; }
            var terms = query.split(/\\s+/);
            var matches = INDEX
                .map(function (e) { return { e: e, s: score(e, terms) }; })
                .filter(function (r) { return r.s > 0; })
                .sort(function (a, b) { return b.s - a.s; })
                .map(function (r) { return r.e; });
            render(matches, box.value.trim());
        });

        box.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') { box.value = ''; list.hidden = true; }
            if (event.key === 'Enter') {
                var first = list.querySelector('a');
                if (first) window.location.href = first.getAttribute('href');
            }
        });

        document.addEventListener('click', function (event) {
            if (!list.contains(event.target) && event.target !== box) list.hidden = true;
        });
    }

    /* ------------------------------------- mark the section being read -- */
    var links = {};
    document.querySelectorAll('.nav li a').forEach(function (a) {
        var href = a.getAttribute('href');
        if (href.charAt(0) === '#') links[href.slice(1)] = a;
    });

    var sections = document.querySelectorAll('main section[id]');
    if (sections.length && Object.keys(links).length && 'IntersectionObserver' in window) {
        var seen = {};
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) { seen[entry.target.id] = entry.isIntersecting; });
            var current = null;
            sections.forEach(function (s) { if (!current && seen[s.id]) current = s.id; });
            Object.keys(links).forEach(function (id) {
                links[id].classList.toggle('is-current', id === current);
            });
        }, { rootMargin: '-10% 0px -70% 0px' });
        sections.forEach(function (s) { observer.observe(s); });
    }
})();
"""

if __name__ == '__main__':
    for name in os.listdir(OUT):
        if name != os.path.basename(HERE):
            path = os.path.join(OUT, name)
            shutil.rmtree(path) if os.path.isdir(path) else os.remove(path)
    build()
