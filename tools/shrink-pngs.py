#!/usr/bin/env python3
"""
Puts the given PNGs on a 256-colour palette, in place.

The share cards come out of a screenshot as full 24-bit PNGs — around a quarter
of a megabyte each — for artwork that is flat colour, one gradient and some
type. A palette with dithering takes about two thirds off and nothing shows: the
type stays crisp because it is a hard edge, and the gradient keeps its shape
because the dither hides the steps.

Called by tools/build-share-cards.cjs. Safe to run again on already-shrunk files.

    python3 tools/shrink-pngs.py public/assets/share/*.png
"""
import os
import sys

try:
    from PIL import Image
except ImportError:
    sys.exit("Pillow is not installed: pip install pillow")


def shrink(path: str) -> tuple[int, int]:
    before = os.path.getsize(path)

    with Image.open(path) as image:
        palette = image.convert("RGB").quantize(
            colors=256,
            method=Image.Quantize.MEDIANCUT,
            dither=Image.Dither.FLOYDSTEINBERG,
        )
        palette.save(path, optimize=True)

    return before, os.path.getsize(path)


def main(paths: list[str]) -> int:
    if not paths:
        sys.exit("Nothing to do — pass one or more PNG paths.")

    for path in paths:
        before, after = shrink(path)
        print(f"  {os.path.basename(path):<28} {before // 1024}kb -> {after // 1024}kb")

    return 0


if __name__ == "__main__":
    raise SystemExit(main(sys.argv[1:]))
