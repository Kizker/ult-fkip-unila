import urllib.request
import json
import os

plantuml_diagrams = {
    "06_activity_diagram_autentikasi.png": """@startuml
skinparam monochrome true
skinparam shadowing false
skinparam defaultFontName Arial
skinparam ActivityFontSize 12

|Pengguna|
start
if (Sudah punya akun?) then (Belum)
  :Isi Form Registrasi;
  :Submit Form;
  |Sistem|
  :Simpan Data & Kirim Email;
  :Verifikasi Email;
else (Sudah)
endif

|Pengguna|
:Buka Halaman Login;

repeat
  |Pengguna|
  :Input Email & Password;
  :Submit Login;
  |Sistem|
  if (Kredensial Valid?) then (Ya)
    :Buat Sesi Login;
    stop
  else (Tidak)
    :Tampilkan Error;
  endif
repeat while ( )
@enduml""",

    "07_activity_diagram_pengajuan.png": """@startuml
skinparam monochrome true
skinparam shadowing false
skinparam defaultFontName Arial
skinparam ActivityFontSize 12

|Mahasiswa|
start
:Login Student Portal;
:Buka Buat Permohonan;
:Pilih Layanan;

repeat
  |Mahasiswa|
  :Isi Formulir Dinamis;
  :Unggah Berkas Prasyarat;
  :Kirim Permohonan;
  |Sistem|
  if (Input Valid?) then (Ya)
    :Simpan Status: Menunggu;
    :Kirim Notifikasi ke Staf;
    stop
  else (Tidak)
    :Tampilkan Peringatan;
  endif
repeat while ( )
@enduml""",

    "08_activity_diagram_verifikasi.png": """@startuml
skinparam monochrome true
skinparam shadowing false
skinparam defaultFontName Arial
skinparam ActivityFontSize 12

|Staf ULT|
start
:Terima Notifikasi;
:Buka Detail Permohonan;
if (Verifikasi Valid?) then (Tidak)
  :Catatan Penolakan;
  |Sistem|
  :Status: Ditolak/Revisi;
  stop
else (Ya)
  |Staf ULT|
  if (Butuh Tanda Tangan?) then (Tidak)
    :Input Nomor Surat;
    |Sistem|
    :Sistem Catat Persetujuan;
  else (Ya)
    |Pejabat Signer|
    :Review Draf Surat;
    :Berikan Tanda Tangan;
    |Sistem|
    :Sistem Catat Persetujuan;
  endif
endif

|Sistem|
:Assembly Engine: Render DOCX/PDF;
:Status: Selesai;
stop
@enduml""",

    "09_activity_diagram_manajemen.png": """@startuml
skinparam monochrome true
skinparam shadowing false
skinparam defaultFontName Arial
skinparam ActivityFontSize 12

|Admin ULT|
start
:Buka Menu Layanan;
:Tambah/Edit Layanan;

repeat
  |Admin ULT|
  :Konfigurasi Syarat;
  :Atur Variabel Input;
  :Unggah Template .docx;
  :Simpan Konfigurasi;
  |Sistem|
  if (Validasi Template?) then (Valid)
    :Simpan Layanan;
    :Update Katalog Publik;
    stop
  else (Error)
    :Tampilkan Peringatan;
  endif
repeat while ( )
@enduml"""
}

output_dir = r"c:\laragon\www\ult-fkip-unila\docs\skripsi\rancangan_diagram"

for fname, code in plantuml_diagrams.items():
    data = {
        "diagram_source": code,
        "diagram_type": "plantuml",
        "output_format": "png"
    }
    req = urllib.request.Request("https://kroki.io/", data=json.dumps(data).encode('utf-8'), headers={'Content-Type': 'application/json'})
    try:
        with urllib.request.urlopen(req) as response:
            filepath = os.path.join(output_dir, fname)
            with open(filepath, 'wb') as f:
                f.write(response.read())
            print(f"Generated {fname}")
    except Exception as e:
        print(f"Failed to generate {fname}: {e}")
