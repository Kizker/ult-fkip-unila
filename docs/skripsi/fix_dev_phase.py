import docx
from docx.shared import Pt
import sys

def move_dev_phase(doc_path):
    print(f"Processing: {doc_path}")
    doc = docx.Document(doc_path)
    
    body = doc._body._body
    
    old_dev_start = -1
    impl_start = -1
    new_dev_start = -1
    
    # Iterate through body elements
    for i, elem in enumerate(body):
        if elem.tag.endswith('p'):
            p = docx.text.paragraph.Paragraph(elem, doc._body)
            text = p.text.strip()
            if text == "Tahap Pengembangan (Development)":
                if old_dev_start == -1:
                    old_dev_start = i
            elif text == "Tahap Implementasi (Implementation)":
                if impl_start == -1:
                    impl_start = i
            elif text == "3. Tahap Pengembangan (Development)":
                if new_dev_start == -1:
                    new_dev_start = i
                    
    print(f"Old Dev Start: {old_dev_start}")
    print(f"Impl Start: {impl_start}")
    print(f"New Dev Start: {new_dev_start}")
    
    if impl_start != -1 and new_dev_start != -1:
        # 1. Collect the elements to move
        elements_to_move = []
        for i in range(new_dev_start, len(body)):
            elements_to_move.append(body[i])
            
        # 2. Collect the elements to delete (old dev content)
        if old_dev_start != -1:
            elements_to_delete = []
            for i in range(old_dev_start, impl_start):
                elements_to_delete.append(body[i])
                
            # 3. Remove old elements
            for elem in elements_to_delete:
                body.remove(elem)
            
        # Let's find the impl_start element again to insert before it
        impl_elem = None
        for elem in body:
            if elem.tag.endswith('p'):
                p = docx.text.paragraph.Paragraph(elem, doc._body)
                if p.text.strip() == "Tahap Implementasi (Implementation)":
                    impl_elem = elem
                    break
        
        if impl_elem is not None:
            # 4. Remove elements_to_move from the end
            for elem in elements_to_move:
                body.remove(elem)
                
            # 5. Insert elements_to_move before impl_elem
            for elem in elements_to_move:
                impl_elem.addprevious(elem)
                
            # Change the heading text to remove "3."
            heading_p = docx.text.paragraph.Paragraph(elements_to_move[0], doc._body)
            heading_p.text = "Tahap Pengembangan (Development)"
            heading_p.style = doc.styles['Heading 3']
            for r in heading_p.runs:
                r.font.name = 'Times New Roman'
                r.font.size = Pt(12)
                r.bold = True
            
            doc.save(doc_path)
            print(f"Successfully moved and updated {doc_path}")
        else:
            print("Could not find Impl element after deletion.")
    else:
        print("Could not find required Impl or New Dev sections.")

move_dev_phase(r"C:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Highlighted.docx")
