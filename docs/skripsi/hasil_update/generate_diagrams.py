import urllib.request
import urllib.parse
import urllib.error
import base64
import json

diagrams = {
    "06_activity_diagram_autentikasi.png": """flowchart TD
    subgraph Pengguna
        A((Mulai)) --> B{Sudah punya akun?}
        B -- Belum --> C[Isi Form Registrasi]
        C --> D[Submit Form]
        B -- Sudah --> E[Buka Halaman Login]
        E --> F[Input Email & Password]
        F --> G[Submit Login]
    end
    subgraph Sistem
        D --> H[Simpan Data & Kirim Email]
        H --> I[Verifikasi Email]
        I --> E
        
        G --> J{Validasi Kredensial}
        J -- Gagal --> K[Tampilkan Error]
        K --> E
        J -- Berhasil --> L[Buat Sesi Login]
    end
    L --> M((Selesai))""",

    "07_activity_diagram_pengajuan.png": """flowchart TD
    subgraph Mahasiswa
        A((Mulai)) --> B[Login Student Portal]
        B --> C[Buka Buat Permohonan]
        C --> D[Pilih Layanan]
        D --> E[Isi Formulir Dinamis]
        E --> F[Unggah Berkas Prasyarat]
        F --> G[Kirim Permohonan]
    end
    subgraph Sistem
        G --> H{Validasi Input}
        H -- Tidak Valid --> I[Tampilkan Peringatan]
        I --> E
        H -- Valid --> J[Simpan Status: Menunggu]
        J --> K[Kirim Notifikasi ke Staf]
    end
    K --> L((Selesai))""",

    "08_activity_diagram_verifikasi.png": """flowchart TD
    subgraph Staf ULT
        A((Mulai)) --> B[Terima Notifikasi]
        B --> C[Buka Detail Permohonan]
        C --> D{Verifikasi Valid?}
        D -- Tidak --> E[Catatan Penolakan]
    end
    subgraph Sistem
        E --> F[Status: Ditolak/Revisi]
    end
    subgraph Staf ULT
        D -- Ya --> H{Butuh Tanda Tangan?}
        H -- Tidak --> I[Input Nomor Surat]
    end
    subgraph Pejabat Signer
        H -- Ya --> J[Review Draf Surat]
        J --> K[Berikan Tanda Tangan]
    end
    subgraph Sistem
        K --> M[Sistem Catat Persetujuan]
        I --> M
        M --> N[Assembly Engine: Render DOCX/PDF]
        N --> O[Status: Selesai]
        O --> P((Selesai))
    end""",

    "09_activity_diagram_manajemen.png": """flowchart TD
    subgraph Admin ULT
        A((Mulai)) --> B[Buka Menu Layanan]
        B --> C[Tambah/Edit Layanan]
        C --> D[Konfigurasi Syarat]
        D --> E[Atur Variabel Input]
        E --> F[Unggah Template .docx]
        F --> G[Simpan Konfigurasi]
    end
    subgraph Sistem
        G --> H{Validasi Template}
        H -- Error --> I[Tampilkan Peringatan]
        I --> F
        H -- Valid --> J[Simpan Layanan]
        J --> K[Update Katalog Publik]
    end
    K --> L((Selesai))"""
}

def generate_image(filename, code):
    # Use mermaid ink API
    b64 = base64.b64encode(code.encode('utf-8')).decode('ascii')
    url = f'https://mermaid.ink/img/{b64}?theme=default&bgColor=white'
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    
    filepath = f"c:\\laragon\\www\\ult-fkip-unila\\docs\\skripsi\\rancangan_diagram\\{filename}"
    try:
        with urllib.request.urlopen(req) as response, open(filepath, 'wb') as out_file:
            out_file.write(response.read())
        print(f"Generated {filename}")
    except urllib.error.URLError as e:
        print(f"Failed to generate {filename}: {e}")

for fname, code in diagrams.items():
    generate_image(fname, code)
