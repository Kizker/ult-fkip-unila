import urllib.request
import zlib
import base64
import os

wireframes = {
    "11_wireframe_beranda": """@startsalt
skinparam handwritten true
skinparam monochrome true
{+
  <b>Web Browser</b>
  ==
  { "<&arrow-left>" | "<&arrow-right>" | "<&reload>" | " https://ult.fkip.unila.ac.id/ " }
  ==
  {
    {/ <b>ULT FKIP Unila</b> | Beranda | Katalog Layanan | Berita | [ Masuk ] }
    --
    {
      .
      .
      <b>Layanan Terpadu Satu Pintu FKIP Unila</b>
      "Portal layanan akademik untuk mahasiswa, dosen, dan tenaga kependidikan."
      [    Lihat Layanan    ] | [  Dashboard  ]
      .
      "14 Layanan   |   5 Pengumuman   |   12 Artikel"
      .
    }
    --
    <b>Layanan Terpopuler</b>
    {
      {+
        <&document>
        <b>Pembuatan Surat Aktif Kuliah</b>
        "Surat Keterangan Aktif Kuliah untuk berbagai keperluan akademik."
      } | {+
        <&document>
        <b>Penerbitan Transkrip Nilai</b>
        "Transkrip Nilai Sementara untuk keperluan beasiswa atau lomba."
      } | {+
        <&document>
        <b>Pengajuan Cuti Akademik</b>
        "Layanan pengajuan cuti sementara bagi mahasiswa aktif."
      }
    }
    --
    { "Kontak: ult@fkip.unila.ac.id" | "Kebijakan Privasi" | "© 2026 ULT FKIP Unila" }
  }
}
@endsalt""",

    "12_wireframe_student": """@startsalt
skinparam handwritten true
skinparam monochrome true
{+
  <b>Web Browser</b>
  ==
  { "<&arrow-left>" | "<&arrow-right>" | "<&reload>" | " https://ult.fkip.unila.ac.id/student/dashboard " }
  ==
  {
    {/ <b>ULT FKIP Unila</b> | Dashboard | Pengajuan | Profil | [ Keluar ] }
    --
    {
      <b>Halo, Mahasiswa!</b>
      "Ringkasan pengajuan terbaru dan status proses layanan Anda."
      [ Ajukan Layanan Baru ]
    }
    --
    {
      {
        <b>Permohonan Terbaru</b>
        {#
          <b>ID</b> | <b>Layanan</b> | <b>Status</b> | <b>Aksi</b>
          REQ-001 | Surat Aktif Kuliah | [ Selesai ] | [ Detail ]
          REQ-002 | Transkrip Nilai | [ Diproses ] | [ Detail ]
          REQ-003 | Surat Izin Observasi | [ Menunggu ] | [ Detail ]
        }
        [ Lihat Semua Permohonan ]
      } | {
        <b>Status Pengajuan</b>
        {
          Total | 5
          Menunggu | 1
          Diproses | 1
          Selesai | 3
        }
      }
    }
  }
}
@endsalt""",

    "13_wireframe_admin": """@startsalt
skinparam handwritten true
skinparam monochrome true
{+
  <b>Web Browser</b>
  ==
  { "<&arrow-left>" | "<&arrow-right>" | "<&reload>" | " https://ult.fkip.unila.ac.id/admin/dashboard " }
  ==
  {
    {/ <b>ULT FKIP Unila</b> | Notifikasi | Profil | [ Keluar ] }
    --
    {
      {
        <b>Menu Navigasi</b>
        * Dashboard
        * Antrian Permohonan
        * Katalog Layanan
        * Manajemen Templat
        * Pengguna & Akses
        * Pengaturan Web
      } | {
        <b>Ringkasan Dashboard</b>
        "Pantau antrian terbaru dan KPI status permohonan."
        [ Kelola Semua Permohonan ]
        --
        {
          <b>Antrian Terbaru</b>
          {#
            <b>Pemohon</b> | <b>Layanan</b> | <b>Status</b> | <b>Aksi</b>
            Andi | Legalisir Ijazah | [ Menunggu ] | [ Tinjau ]
            Budi | Surat Observasi | [ Menunggu ] | [ Tinjau ]
            Citra | Bebas Perpus | [ Diproses ] | [ Tinjau ]
          }
        }
      }
    }
  }
}
@endsalt"""
}

for name, code in wireframes.items():
    compressed = zlib.compress(code.encode('utf-8'), 9)
    encoded = base64.urlsafe_b64encode(compressed).decode('ascii')
    url = f"https://kroki.io/plantuml/png/{encoded}"
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    try:
        with urllib.request.urlopen(req) as response:
            filepath = rf"c:\laragon\www\ult-fkip-unila\docs\skripsi\rancangan_diagram\{name}.png"
            with open(filepath, 'wb') as f:
                f.write(response.read())
            print(f"Generated {name}.png!")
    except Exception as e:
        print(f"Failed to generate {name}: {e}")

