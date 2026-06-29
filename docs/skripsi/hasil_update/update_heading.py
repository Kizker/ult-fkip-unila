import docx

def update_heading(filename):
    doc = docx.Document(filename)
    
    for p in doc.paragraphs:
        if p.style.name.startswith('Heading 2') and ("Analisis Kurikulum Pendidikan Teknologi Informasi" in p.text or "Analisis Kurikulum Dasar-dasar Pengembangan Perangkat Lunak dan Gim (PPLG)" in p.text):
            p.text = "Analisis Relevansi Kurikulum PPLG"
            
    doc.save(filename)
    print(f"Successfully updated {filename}")

update_heading("001_Skripsi_Andricha Dea Mitra_Clean.docx")
update_heading("001_Skripsi_Andricha Dea Mitra_Highlighted.docx")
