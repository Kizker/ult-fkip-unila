import zipfile

def list_media(doc_path):
    with zipfile.ZipFile(doc_path, 'r') as doc_zip:
        media_files = [f for f in doc_zip.namelist() if f.startswith('word/media/')]
        print(f"Total media files in {doc_path}: {len(media_files)}")
        for f in media_files:
            size = doc_zip.getinfo(f).file_size
            print(f" - {f}: {size} bytes")

if __name__ == '__main__':
    list_media('001_Skripsi_Andricha Dea Mitra_Clean.docx')
    print("-------------------------------------------------")
    list_media(r'c:\laragon\www\ult-fkip-unila\docs\skripsi\update skripsi terakhir-seminar proposal\001_Skripsi_Andricha Dea Mitra.docx')
