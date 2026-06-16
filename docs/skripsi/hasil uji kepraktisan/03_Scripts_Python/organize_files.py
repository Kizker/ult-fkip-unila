import os
import shutil
import glob

def organize_workspace():
    root_dir = os.getcwd()
    print(f"Mengorganisasi file di: {root_dir}\n")

    # Tentukan folder tujuan
    folders = {
        "01_Kuesioner_Responden_PDF": "Folder untuk 18 berkas PDF kuesioner dari responden.",
        "02_Database_OMR_JSON": "Folder untuk menyimpan data hasil ekstraksi kuesioner (JSON).",
        "03_Scripts_Python": "Folder untuk menyimpan script python pengolahan data.",
        "04_Template_dan_Referensi": "Folder untuk template kosong, rekap komentar PDF, dan file backup."
    }

    # Buat folder jika belum ada
    for folder, desc in folders.items():
        folder_path = os.path.join(root_dir, folder)
        if not os.path.exists(folder_path):
            os.makedirs(folder_path)
            print(f"[BUAT FOLDER] {folder} - {desc}")
        else:
            print(f"[FOLDER SUDAH ADA] {folder}")

    print("-" * 50)

    # 1. Pindahkan file PDF Responden (uji k *.pdf) ke 01_Kuesioner_Responden_PDF
    respondent_pdfs = glob.glob(os.path.join(root_dir, "uji k *.pdf"))
    for pdf in respondent_pdfs:
        dest = os.path.join(root_dir, "01_Kuesioner_Responden_PDF", os.path.basename(pdf))
        shutil.move(pdf, dest)
        print(f"[MOVE] {os.path.basename(pdf)} -> 01_Kuesioner_Responden_PDF/")

    # 2. Pindahkan file JSON ke 02_Database_OMR_JSON
    json_files = glob.glob(os.path.join(root_dir, "*.json"))
    for jfile in json_files:
        dest = os.path.join(root_dir, "02_Database_OMR_JSON", os.path.basename(jfile))
        shutil.move(jfile, dest)
        print(f"[MOVE] {os.path.basename(jfile)} -> 02_Database_OMR_JSON/")

    # 3. Pindahkan template, referensi, dan backup ke 04_Template_dan_Referensi
    ref_files = [
        "instrumen-uji-kepraktisan-FINAL.pdf",
        "Rekap_Komentar_Saran.pdf",
        "Rekap_Uji_Kepraktisan_BACKUP.xlsx"
    ]
    for rfile in ref_files:
        rfile_path = os.path.join(root_dir, rfile)
        if os.path.exists(rfile_path):
            dest = os.path.join(root_dir, "04_Template_dan_Referensi", rfile)
            shutil.move(rfile_path, dest)
            print(f"[MOVE] {rfile} -> 04_Template_dan_Referensi/")

    # 4. Pindahkan semua file Python (.py) kecuali script ini sendiri ke 03_Scripts_Python
    py_files = glob.glob(os.path.join(root_dir, "*.py"))
    for pyfile in py_files:
        filename = os.path.basename(pyfile)
        if filename != "organize_files.py":
            dest = os.path.join(root_dir, "03_Scripts_Python", filename)
            shutil.move(pyfile, dest)
            print(f"[MOVE] {filename} -> 03_Scripts_Python/")

    print("\nWorkspace berhasil dirapikan!")
    print("Hanya file utama 'Rekap_Uji_Kepraktisan.xlsx' dan 'AGENTS.md' yang berada di root folder.")

if __name__ == "__main__":
    organize_workspace()
