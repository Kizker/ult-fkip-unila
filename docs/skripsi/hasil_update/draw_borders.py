from PIL import Image, ImageDraw
import glob

def process(img_path):
    img = Image.open(img_path).convert('RGB')
    w, h = img.size
    pixels = img.load()
    
    # find vertical lines (columns with > h*0.8 black pixels)
    # in plantuml, a pixel might not be exactly (0,0,0) due to antialiasing, but the core line is.
    v_lines = []
    for x in range(w):
        c = sum(1 for y in range(h) if pixels[x, y] == (0,0,0))
        if c > h * 0.8:
            v_lines.append(x)
            
    if not v_lines:
        print("No vertical lines found for", img_path)
        return
        
    min_x = min(v_lines)
    max_x = max(v_lines)
    
    x = min_x
    top_y = next(y for y in range(h) if pixels[x, y] == (0,0,0))
    bottom_y = next(y for y in reversed(range(h)) if pixels[x, y] == (0,0,0))
    
    # Find where header text ends
    # We look between top_y+1 and top_y+100
    # The text starts around top_y+5, and ends around top_y+25
    header_y = top_y + 35 # fallback
    
    for y in range(top_y + 1, top_y + 100):
        # count black pixels in this row between min_x and max_x, excluding the vertical lines themselves
        # A margin of 2 pixels around vertical lines is excluded
        excluded_x = set()
        for vx in v_lines:
            excluded_x.update([vx-1, vx, vx+1])
            
        c = sum(1 for vx in range(min_x, max_x+1) if pixels[vx, y] == (0,0,0) and vx not in excluded_x)
        
        # We want to find the bottom of the text.
        # The text has black pixels.
        if c > 0:
            pass # still in text
        else:
            # We hit an empty row!
            # Let's verify it's really empty for the next few rows
            empty_count = 0
            for ny in range(y, y+5):
                nc = sum(1 for vx in range(min_x, max_x+1) if pixels[vx, ny] == (0,0,0) and vx not in excluded_x)
                if nc == 0:
                    empty_count += 1
            
            if empty_count == 5 and y > top_y + 10:
                header_y = y + 8 # 8px padding
                break
                
    draw = ImageDraw.Draw(img)
    # top line
    draw.line([(min_x, top_y), (max_x, top_y)], fill="black", width=1)
    # bottom line
    draw.line([(min_x, bottom_y), (max_x, bottom_y)], fill="black", width=1)
    # header separator line
    draw.line([(min_x, header_y), (max_x, header_y)], fill="black", width=1)
    
    img.save(img_path)
    print(f"Processed {img_path}: min_x={min_x}, max_x={max_x}, top_y={top_y}, bottom_y={bottom_y}, header_y={header_y}")

for f in glob.glob(r"c:\laragon\www\ult-fkip-unila\docs\skripsi\rancangan_diagram\0*.png"):
    process(f)
