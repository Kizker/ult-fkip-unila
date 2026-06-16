import docx
import shutil

doc_clean_path = r'c:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Clean.docx'
doc_hl_path = r'c:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Highlighted.docx'

def fix_document(file_path):
    doc = docx.Document(file_path)
    
    uc_idx = -1
    arch_idx = -1
    
    for i, p in enumerate(doc.paragraphs):
        # 1. ADDIE Design Phase headings
        if 'Berdasarkan kerangka perancangan dalam model ADDIE, tahap perancangan ini meliputi empat langkah' in p.text:
            p.text = "Pada tahap perancangan (Design) dalam model pengembangan ADDIE, fokus utama peneliti dialihkan dari perumusan masalah konseptual menuju perancangan solusi teknis. Tahap perancangan ini meliputi penyusunan cetak biru (blueprint) sistem yang terdiri atas empat langkah utama, yaitu:"
            for run in p.runs:
                run.font.name = 'Times New Roman'
                run.font.size = docx.shared.Pt(12)
        elif 'Constructing Criterion-Referenced Tests' in p.text:
            p.text = "a. Penyusunan Instrumen Evaluasi"
            for run in p.runs:
                run.bold = True
                run.font.name = 'Times New Roman'
                run.font.size = docx.shared.Pt(12)
        elif 'Media Selection' in p.text:
            p.text = "b. Pemilihan Teknologi dan Arsitektur"
            for run in p.runs:
                run.bold = True
                run.font.name = 'Times New Roman'
                run.font.size = docx.shared.Pt(12)
        elif 'Format Selection' == p.text.strip():
            p.text = "c. Perancangan Format Antarmuka"
            for run in p.runs:
                run.bold = True
                run.font.name = 'Times New Roman'
                run.font.size = docx.shared.Pt(12)
        elif 'Initial Design' == p.text.strip():
            p.text = "d. Pemodelan Sistem Awal"
            for run in p.runs:
                run.bold = True
                run.font.name = 'Times New Roman'
                run.font.size = docx.shared.Pt(12)
        elif 'Langkah perancangan awal (Initial Design)' in p.text:
            p.text = p.text.replace('Langkah perancangan awal (Initial Design)', 'Langkah pemodelan sistem awal')
            
        # 2. Find markers for Use Case and Architecture Diagram
        if 'Gambar 4. Diagram Use Case' in p.text:
            uc_idx = i
        if 'Gambar 5. Diagram Arsitektur' in p.text:
            arch_idx = i

    print(f"File: {file_path}")
    print(f"Gambar 4 at: {uc_idx}, Gambar 5 at: {arch_idx}")
    
    # We will use another script or method to inject paragraphs to avoid messing up docx structure
    # For now, let's just save the header changes.
    doc.save(file_path)
    print("Saved headers.")

fix_document(doc_clean_path)
fix_document(doc_hl_path)
