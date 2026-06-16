from __future__ import annotations

from pathlib import Path

from PIL import Image


ROOT = Path(__file__).resolve().parents[1]
SCREENSHOT_DIR = ROOT / "docs" / "buku-panduan" / "assets" / "screenshots"


def row_has_content(img: Image.Image, y: int) -> bool:
    width, _ = img.size
    darkish = 0
    colorful = 0

    for x in range(0, width, 3):
        r, g, b = img.getpixel((x, y))[:3]
        if min(r, g, b) < 232:
            darkish += 1
        elif max(r, g, b) - min(r, g, b) > 16:
            colorful += 1

        if darkish >= 8 or colorful >= 12:
            return True

    return False


def crop_bottom(path: Path, padding: int = 28) -> None:
    with Image.open(path) as original:
        img = original.convert("RGBA")
        width, height = img.size
        bottom = height - 1

        while bottom > 220 and not row_has_content(img, bottom):
            bottom -= 1

        target_height = min(height, bottom + 1 + padding)
        if target_height < height:
            cropped = original.crop((0, 0, width, target_height))
            cropped.save(path)


def main() -> None:
    for index in range(41, 74):
        matches = sorted(SCREENSHOT_DIR.glob(f"{index:02d}-*.png"))
        for path in matches:
            crop_bottom(path)
            print(f"cropped {path.name}")


if __name__ == "__main__":
    main()
