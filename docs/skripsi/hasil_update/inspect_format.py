import docx

def inspect_format(filename):
    doc = docx.Document(filename)
    print("Inspecting formatting of early paragraphs...")
    count = 0
    for p in doc.paragraphs:
        text = p.text.strip()
        if len(text) > 50 and p.style.name == 'Normal':
            print(f"Alignment: {p.alignment}")
            print(f"First Line Indent: {p.paragraph_format.first_line_indent}")
            print(f"Left Indent: {p.paragraph_format.left_indent}")
            print(f"Space Before: {p.paragraph_format.space_before}")
            print(f"Space After: {p.paragraph_format.space_after}")
            print(f"Text snippet: {text[:50]}...")
            count += 1
            if count >= 3:
                break

inspect_format("001_Skripsi_Andricha Dea Mitra_Clean.docx")
