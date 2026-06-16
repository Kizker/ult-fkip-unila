import pdfplumber
import sys

def test_extract(pdf_path):
    print(f"Reading: {pdf_path}")
    try:
        with pdfplumber.open(pdf_path) as pdf:
            for i, page in enumerate(pdf.pages):
                print(f"--- Page {i+1} ---")
                text = page.extract_text()
                print("TEXT:")
                print(text)
                
                print("\nTABLES:")
                tables = page.extract_tables()
                if tables:
                    for j, table in enumerate(tables):
                        print(f"Table {j+1}:")
                        for row in table:
                            print(row)
                else:
                    print("No tables found on this page.")
                print("="*50)
    except Exception as e:
        print(f"Error reading PDF: {e}")

if __name__ == '__main__':
    if len(sys.argv) > 1:
        test_extract(sys.argv[1])
    else:
        print("Please provide a path to a PDF file.")
