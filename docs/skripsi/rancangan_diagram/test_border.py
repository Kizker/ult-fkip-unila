from PIL import Image, ImageDraw
import sys

def process(img_path):
    img = Image.open(img_path).convert('RGB')
    w, h = img.size
    
    # Find columns with the most black pixels
    # A pixel is "black" if R<50, G<50, B<50
    pixels = img.load()
    col_counts = []
    for x in range(w):
        count = 0
        for y in range(h):
            r, g, b = pixels[x, y]
            if r < 50 and g < 50 and b < 50:
                count += 1
        col_counts.append(count)
        
    max_count = max(col_counts)
    # the vertical line column should have a count close to max_count (e.g. > max_count * 0.8)
    line_x = -1
    for x in range(w):
        if col_counts[x] == max_count:
            line_x = x
            break
            
    if line_x != -1:
        # Find the top-most black pixel in this column
        top_y = -1
        for y in range(h):
            r, g, b = pixels[line_x, y]
            if r < 50 and g < 50 and b < 50:
                top_y = y
                break
                
        if top_y != -1:
            print(f"{img_path}: Vertical line at x={line_x}, starts at y={top_y}")
            # Draw outer border
            draw = ImageDraw.Draw(img)
            draw.rectangle([0, 0, w-1, h-1], outline="black", width=2)
            
            # Draw horizontal line at top_y
            draw.line([(0, top_y), (w-1, top_y)], fill="black", width=1)
            
            img.save(img_path)
            print("Successfully processed!")
        else:
            print("Could not find top_y")
    else:
        print("Could not find line_x")

if __name__ == "__main__":
    process(r"c:\laragon\www\ult-fkip-unila\docs\skripsi\rancangan_diagram\06_activity_diagram_autentikasi.png")
