import os
import numpy as np
from PIL import Image

img_path = r'C:\Users\Andri\.gemini\antigravity-ide\brain\8a482603-602c-4a1e-b39a-b55525be5686\01-beranda-sistem.png'
out_path = r'C:\laragon\www\ult-fkip-unila\docs\skripsi\rancangan_diagram\screenshots\01-beranda-sistem.jpg'

img = Image.open(img_path).convert('RGB')
max_width = 1366
ratio = max_width / float(img.width)
new_height = int((float(img.height) * float(ratio)))
img = img.resize((max_width, new_height), Image.Resampling.LANCZOS)

# Convert to numpy array
arr = np.array(img)

# Calculate variance/std dev of each row. 
# We'll compute the std dev of the RGB values across the width for each row.
# To be robust, we can compute the std dev for each color channel and sum them.
row_std = np.std(arr, axis=1).sum(axis=1)

print(f"Total rows: {len(row_std)}")

# Let's define a threshold for "empty gradient".
# A smooth gradient will have very low standard deviation horizontally.
# But there might be noise. Let's find the median std dev of the top 20% rows.
# Actually, let's just print out the regions.
is_empty = row_std < 10.0  # Adjust threshold based on testing

# Let's find contiguous blocks of empty rows
blocks = []
in_block = False
start = 0

for i, empty in enumerate(is_empty):
    if empty and not in_block:
        in_block = True
        start = i
    elif not empty and in_block:
        in_block = False
        if (i - start) > 100:  # Only care about blocks larger than 100px
            blocks.append((start, i))

print(f"Empty blocks found: {blocks}")

# We will crop out the middle of each large empty block, leaving 50px on each side.
keep_rows = np.ones(new_height, dtype=bool)
for start, end in blocks:
    # If the block is very large, cut out the middle
    cut_start = start + 50
    cut_end = end - 50
    if cut_end > cut_start:
        keep_rows[cut_start:cut_end] = False
        print(f"Cutting from {cut_start} to {cut_end} (Removed {cut_end - cut_start}px)")

# Now extract the kept rows
kept_arr = arr[keep_rows]

# Convert back to image
final_img = Image.fromarray(kept_arr)
final_img.save(out_path, "JPEG", quality=90)
print(f"Auto-crop complete. New height: {final_img.height}px")
