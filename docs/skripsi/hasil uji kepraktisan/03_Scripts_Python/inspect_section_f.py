import fitz

def inspect():
    doc = fitz.open('instrumen-uji-kepraktisan-FINAL.pdf')
    page = doc[2] # Page 2 is the 3rd page (0-indexed)
    
    # Search for text elements
    search_terms = ["F. Kesimpulan", "Sangat praktis", "Praktis untuk", "Cukup praktis", "Kurang praktis", "Tidak praktis"]
    print("Page 2 Text Elements:")
    for term in search_terms:
        rects = page.search_for(term)
        for r in rects:
            print(f"  '{term}': {r}")
            
    doc.close()

if __name__ == "__main__":
    inspect()
