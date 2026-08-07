# Rebuilding the system map

The site one level up is generated. Edit it here, not there — anything written
directly into the HTML pages is overwritten on the next build.

## The files

| File | What it is |
| --- | --- |
| `map.html` | **The source.** All twenty-one sections, their prose, tables and diagram definitions, in one file. |
| `build.py` | Splits `map.html` into the seven pages, inlines the diagrams, writes the stylesheet and the search index. |
| `build-single.py` | Builds the same content as one self-contained file, for sending to somebody. Run it after `build.py`. |
| `render-diagrams.js` | Turns the mermaid definitions in `map.html` into the finished SVGs in `svg/`. |
| `svg/` | The rendered diagrams. Generated — do not edit by hand. |
| `fonts.css` | Space Grotesk and Manrope, embedded so the site needs no network. |

## Changing the words

Edit the prose in `map.html`, then:

```bash
python3 build.py          # the seven-page site
python3 build-single.py   # the one-file version
```

That is the whole loop. No diagrams are re-rendered, so it takes a second.

## Changing a diagram

The diagrams are [mermaid](https://mermaid.js.org) definitions inside
`<pre class="mermaid">` blocks in `map.html`. Edit the definition, then re-render
and rebuild:

```bash
npm install                        # once
node render-diagrams.js            # writes svg/fig-NN.svg
python3 build.py                   # inlines them into the pages
```

Diagrams are matched to figures **by their order in `map.html`**. Adding one in
the middle renumbers everything after it, which is fine — the whole set is
re-rendered each time — but do re-run both commands, not just the second.

## Why the diagrams are pre-rendered

Shipping mermaid itself would add 3.5 MB to a set of pages that are otherwise a
few hundred kilobytes, and it would make every reader's browser lay the diagrams
out again on each visit. Worse, mermaid measures label widths before it paints:
if the font it measures with has not loaded yet, every label overflows its box.
Rendering once, here, removes all of that. The published site carries no diagram
code at all.

## Adding a section

1. Add a `<section id="...">` to `map.html`, following the shape of its
   neighbours — a `.sec-head` with its number and `<h2>`, then prose, then a
   `<figure>` if it needs one, then the `.files` list.
2. Add its id to the right page in the `PAGES` list in `build.py`.
3. Re-render and rebuild.

The sidebar, the search index, the previous/next links and the section numbering
all follow from those two edits.
