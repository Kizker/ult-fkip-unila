import urllib.request
import zlib
import base64
import os

wireframe_code = """@startsalt
skinparam monochrome true
skinparam shadowing false
skinparam defaultFontName Arial

{+
  {/ <b>Logo ULT FKIP Unila</b> | Beranda | Katalog Layanan | Berita | [Masuk / Daftar] }
  --
  {
    .
    .
    <b>Slogan Layanan Terpadu FKIP Unila</b>
    "Sistem informasi pelayanan akademik digital yang cepat, transparan, dan mudah."
    [       Mulai Ajukan Permohonan Sekarang!       ]
    .
    .
  }
  --
  <b>Panduan Alur Permohonan</b>
  {
    {+
      <b>1. Pilih Layanan</b>
      "Cari layanan dokumen"
    } | {+
      <b>2. Isi Data</b>
      "Lengkapi formulir"
    } | {+
      <b>3. Verifikasi</b>
      "Tunggu proses staf"
    } | {+
      <b>4. Selesai</b>
      "Dokumen diunduh"
    }
  }
  --
  { "Kontak Institusi" | "Tautan Regulasi" | "Hak Cipta © 2026 ULT FKIP Unila" }
}
@endsalt"""

compressed = zlib.compress(wireframe_code.encode('utf-8'), 9)
encoded = base64.urlsafe_b64encode(compressed).decode('ascii')

url = f"https://kroki.io/plantuml/png/{encoded}"
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
try:
    with urllib.request.urlopen(req) as response:
        filepath = r"c:\laragon\www\ult-fkip-unila\docs\skripsi\rancangan_diagram\10_wireframe_beranda.png"
        with open(filepath, 'wb') as f:
            f.write(response.read())
        print(f"Generated Wireframe!")
except Exception as e:
    print(f"Failed to generate: {e}")
