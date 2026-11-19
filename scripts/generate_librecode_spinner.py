#!/usr/bin/env python3
# SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
# SPDX-License-Identifier: AGPL-3.0-or-later
"""
Generate the independently branded LibreCode loader. Development-only; Pillow is
not required by the runtime module. Source mark originates at librecode.coop.
The animation NEVER uses the Akaunting loading icon or its branded green ring.
"""
from __future__ import annotations

import argparse
from pathlib import Path
from math import cos, pi
from PIL import Image, ImageDraw, ImageFont

ROOT = Path(__file__).resolve().parents[1]
DEFAULT_MARK = ROOT / "Resources/assets/spinners/librecode-mark.png"
DEFAULT_TARGET = ROOT / "Resources/assets/spinners/librecode-loading.gif"


def render(mark_path: Path, target: Path) -> None:
    width, height = 238, 96
    scale = 3
    size = (width * scale, height * scale)
    mark = Image.open(mark_path).convert("RGBA")
    mark.thumbnail((55 * scale, 47 * scale), Image.Resampling.LANCZOS)

    font_path = Path("/usr/share/fonts/truetype/lato/Lato-Bold.ttf")
    if not font_path.is_file():
        raise FileNotFoundError("Lato-Bold.ttf is needed only to regenerate the branded sample")
    font = ImageFont.truetype(str(font_path), 29 * scale)

    frames = []
    for i in range(24):
        image = Image.new("RGBA", size, (0, 0, 0, 0))
        art = ImageDraw.Draw(image)
        image.alpha_composite(mark, (13 * scale, 19 * scale))

        # Full LibreCode wordmark: readable against both light and dark surfaces.
        art.text(
            (76 * scale, 19 * scale),
            "LibreCode", font=font, anchor="lt", fill=(241, 245, 249, 255),
            stroke_width=scale, stroke_fill=(30, 41, 59, 255),
        )

        # Subtle sequential red pulsing dots, distinct from Akaunting's ring.
        for j in range(3):
            phase = (i / 24 - j / 3) * 2 * pi
            intensity = (1 + cos(phase)) / 2
            radius = (2.4 + 1.9 * intensity) * scale
            cx = (141 + j * 22) * scale
            cy = 73 * scale
            red = (229, 51, 42, round(112 + 143 * intensity))
            art.ellipse((cx-radius, cy-radius, cx+radius, cy+radius), fill=red)

        frames.append(image.resize((width, height), Image.Resampling.LANCZOS))

    target.parent.mkdir(parents=True, exist_ok=True)
    frames[0].save(
        target, format="GIF", save_all=True, append_images=frames[1:],
        duration=75, loop=0, disposal=2, optimize=True, transparency=0,
    )
    check = Image.open(target)
    assert check.n_frames == 24
    assert check.size == (width, height)
    assert check.info.get("loop") == 0
    for index in (0, 6, 12, 18):
        check.seek(index)
        assert check.convert("RGBA").getpixel((0, 0))[3] == 0
    if target.stat().st_size > 1024 * 1024:
        raise ValueError("Sample GIF exceeds the module upload limit")


if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument("--mark", type=Path, default=DEFAULT_MARK)
    parser.add_argument("--output", type=Path, default=DEFAULT_TARGET)
    args = parser.parse_args()
    render(args.mark, args.output)
    print("Branded LibreCode spinner created:", args.output)
