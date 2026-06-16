import fitz
import cv2
import numpy as np
from PIL import Image
import os
import glob

def crop_conclusions():
    os.makedirs('C:\\Users\\Andri\\.gemini\\antigravity-ide\\brain\\2028e87a-9dec-4a6e-87d9-80b5899bba06\\scratch\\conclusions', exist_ok=True)
    
    tpl_doc = fitz.open('instrumen-uji-kepraktisan-FINAL.pdf')
    scale = 200.0 / 72.0
    tpl_p2_pix = tpl_doc[2].get_pixmap(dpi=200)
    tpl_p2_img = Image.frombytes("RGB", [tpl_p2_pix.width, tpl_p2_pix.height], tpl_p2_pix.samples)
    tpl_p2_cv = cv2.cvtColor(np.array(tpl_p2_img), cv2.COLOR_RGB2GRAY)
    
    orb = cv2.ORB_create(nfeatures=5000)
    kp_tpl_p2, des_tpl_p2 = orb.detectAndCompute(tpl_p2_cv, None)
    bf = cv2.BFMatcher(cv2.NORM_HAMMING, crossCheck=True)
    
    pdf_files = sorted(glob.glob("uji k *.pdf"))
    
    # We will use the same best dy found in our previous step
    # Map of filename to dy
    dy_map = {
        "uji k admin anisa.pdf": 13,
        "uji k admin lisa.pdf": 1,
        "uji k admin riswan.pdf": 8,
        "uji k pbs khaerul .pdf": 0,
        "uji k pbs martin.pdf": 0,
        "uji k pbs nurani.pdf": 3,
        "uji k pip aulia.pdf": 13,
        "uji k pip nazwa.pdf": 4,
        "uji k pip salsa.pdf": -3,
        "uji k pips andhini.pdf": 9,
        "uji k pips arya.pdf": -2,
        "uji k pips mita.pdf": 13,
        "uji k pmipa nabila.pdf": 13,
        "uji k pmipa nur .pdf": 2,
        "uji k pmipa rizky.pdf": 0,
        "uji k ult agus.pdf": 6,
        "uji k ult amrul.pdf": 6,
        "uji k ult tri .pdf": -5
    }
    
    # Target y positions for checkboxes a, b, c, d, e in template points (72 dpi)
    # a: 356.5, b: 372.4, c: 388.2, d: 404.1, e: 420.1
    # Checkboxes are located at x: 72.0 to 90.0 (points)
    
    for fn in pdf_files:
        doc = fitz.open(fn)
        pix = doc[2].get_pixmap(dpi=200)
        img = Image.frombytes("RGB", [pix.width, pix.height], pix.samples)
        gray = cv2.cvtColor(np.array(img), cv2.COLOR_RGB2GRAY)
        
        # Align
        kp, des = orb.detectAndCompute(gray, None)
        matches = bf.match(des_tpl_p2, des)
        matches = sorted(matches, key=lambda x: x.distance)
        
        pts_tpl = np.float32([kp_tpl_p2[m.queryIdx].pt for m in matches[:150]]).reshape(-1, 1, 2)
        pts_resp = np.float32([kp[m.trainIdx].pt for m in matches[:150]]).reshape(-1, 1, 2)
        H, _ = cv2.findHomography(pts_resp, pts_tpl, cv2.RANSAC, 5.0)
        aligned = cv2.warpPerspective(gray, H, (tpl_p2_cv.shape[1], tpl_p2_cv.shape[0]))
        
        dy = dy_map.get(fn, 0)
        
        # Crop the whole conclusion block to verify visually
        # y: 340 to 440 (points), scale it and add shift
        y0_pt, y1_pt = 345, 435
        x0_pt, x1_pt = 65, 300
        
        ry0 = int(y0_pt * scale) + dy
        ry1 = int(y1_pt * scale) + dy
        rx0 = int(x0_pt * scale)
        rx1 = int(x1_pt * scale)
        
        crop = aligned[ry0:ry1, rx0:rx1]
        
        # Save crop
        out_name = os.path.basename(fn).replace(".pdf", "_conclusion.png")
        out_path = os.path.join('C:\\Users\\Andri\\.gemini\\antigravity-ide\\brain\\2028e87a-9dec-4a6e-87d9-80b5899bba06\\scratch\\conclusions', out_name)
        cv2.imwrite(out_path, crop)
        
        # Let's perform precise OMR on the checkboxes!
        # Checkboxes are at x: 72 to 88 (points), y at: a=356.5, b=372.4, c=388.2, d=404.1, e=420.1
        y_centers = [356.5, 372.4, 388.2, 404.1, 420.1]
        option_mins = []
        option_counts = []
        for i, yc in enumerate(y_centers):
            cy = int(yc * scale) + dy
            cx = int(80.0 * scale) # Center of checkbox (approx x=72 to 88)
            
            # Checkbox cell: size approx 20x20 in scale 200dpi
            cell = aligned[cy-8:cy+8, cx-8:cx+8]
            # Shave 15% to avoid borders
            shaved = cell[2:-2, 2:-2]
            
            option_mins.append(np.min(shaved))
            option_counts.append(np.sum(shaved < 150))
            
        # The option with the lowest minimum pixel (darkest mark) or highest count
        best_opt_idx = np.argmin(option_mins)
        best_opt_letter = chr(ord('a') + best_opt_idx)
        
        print(f"{fn} (dy={dy}): auto_OMR={best_opt_letter} | mins={option_mins} | counts={option_counts}")
        
        doc.close()
    tpl_doc.close()

if __name__ == "__main__":
    crop_conclusions()
