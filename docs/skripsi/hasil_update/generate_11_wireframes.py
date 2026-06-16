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

    "12_wireframe_katalog": """@startsalt
skinparam handwritten true
skinparam monochrome true
{+
  <b>Web Browser</b>
  ==
  { "<&arrow-left>" | "<&arrow-right>" | "<&reload>" | " https://ult.fkip.unila.ac.id/services " }
  ==
  {
    {/ <b>ULT FKIP Unila</b> | Beranda | <b>Katalog Layanan</b> | Berita | [ Masuk ] }
    --
    {
      <b>Katalog Layanan Akademik</b>
      "Daftar lengkap layanan persuratan fakultas beserta rincian syarat dokumen."
      [ Cari Layanan... ]
    }
    --
    {
      {+
        <b>Surat Aktif Kuliah</b>
        * Fotokopi KTM
        * Slip SPP Terakhir
        [ Detail Persyaratan ]
      } | {+
        <b>Transkrip Nilai</b>
        * Pas Foto
        * Bukti Lunas UKT
        [ Detail Persyaratan ]
      } | {+
        <b>Cuti Akademik</b>
        * Surat Bebas Perpus
        * Surat Permohonan
        [ Detail Persyaratan ]
      }
    }
  }
}
@endsalt""",

    "13_wireframe_berita": """@startsalt
skinparam handwritten true
skinparam monochrome true
{+
  <b>Web Browser</b>
  ==
  { "<&arrow-left>" | "<&arrow-right>" | "<&reload>" | " https://ult.fkip.unila.ac.id/blog " }
  ==
  {
    {/ <b>ULT FKIP Unila</b> | Beranda | Katalog Layanan | <b>Berita</b> | [ Masuk ] }
    --
    {
      <b>Berita & Pengumuman Resmi</b>
      "Daftar artikel berita terbaru dan pengumuman dari fakultas."
    }
    --
    {
      {
        [X]
        <b>Jadwal Wisuda Periode III</b>
        "Pendaftaran wisuda akan dibuka mulai tanggal..."
        (12 Mei 2026)
      } | {
        [X]
        <b>Perubahan Jam Pelayanan</b>
        "Selama bulan puasa, jam pelayanan ULT FKIP..."
        (10 Mei 2026)
      }
    }
    --
    "   < Sebelumnya   |   [1]   |   2   |   3   |   Selanjutnya >   "
  }
}
@endsalt""",

    "14_wireframe_login": """@startsalt
skinparam handwritten true
skinparam monochrome true
{+
  <b>Web Browser</b>
  ==
  { "<&arrow-left>" | "<&arrow-right>" | "<&reload>" | " https://ult.fkip.unila.ac.id/login " }
  ==
  {
    {/ <b>ULT FKIP Unila</b> | Beranda | Katalog | Berita | [ Masuk ] }
    --
    {
      .
      {+
        <&person> <b>Masuk ke Sistem</b>
        "Gunakan akun SSO Unila atau Email Anda"
        --
        "Email Address:"
        [ andi@students.unila.ac.id      ]
        "Password:"
        [ ************************       ]
        [] Ingat Saya
        [       Masuk ke Dasbor       ]
        "Lupa kata sandi?" | "Belum punya akun? Daftar"
      }
      .
    }
  }
}
@endsalt""",

    "15_wireframe_student": """@startsalt
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
          REQ-003 | Izin Observasi | [ Menunggu ] | [ Detail ]
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

    "16_wireframe_form": """@startsalt
skinparam handwritten true
skinparam monochrome true
{+
  <b>Web Browser</b>
  ==
  { "<&arrow-left>" | "<&arrow-right>" | "<&reload>" | " .../student/requests/create " }
  ==
  {
    {/ <b>ULT FKIP Unila</b> | Dashboard | Pengajuan | Profil | [ Keluar ] }
    --
    {
      <b>Form Pengajuan: Surat Aktif Kuliah</b>
      "Lengkapi formulir isian dinamis di bawah ini dan unggah berkas prasyarat."
      --
      "NPM:"
      [ 2213023001                       ]
      "Keperluan Pembuatan Surat:"
      [ Beasiswa / Tunjangan Gaji Orang Tua     ]
      "Nomor HP / WhatsApp:"
      [ 081234567890                     ]
      --
      <b>Berkas Prasyarat (Maks 2MB, PDF/JPG)</b>
      "Scan KTM:"
      [ Browse... ] "KTM_Andi.pdf"
      "Scan Slip SPP Terakhir:"
      [ Browse... ] "Tidak ada file yang dipilih"
      --
      [ Batal ] | [ Simpan & Kirim Permohonan ]
    }
  }
}
@endsalt""",

    "17_wireframe_timeline": """@startsalt
skinparam handwritten true
skinparam monochrome true
{+
  <b>Web Browser</b>
  ==
  { "<&arrow-left>" | "<&arrow-right>" | "<&reload>" | " .../student/requests/REQ-001 " }
  ==
  {
    {/ <b>ULT FKIP Unila</b> | Dashboard | Pengajuan | Profil | [ Keluar ] }
    --
    {
      {
        <b>Detail Permohonan (REQ-001)</b>
        "Layanan: Surat Aktif Kuliah"
        "Tanggal: 12 Mei 2026"
        --
        <b>Data Isian:</b>
        NPM: 2213023001
        Keperluan: Beasiswa
        --
        <b>Berkas Lampiran:</b>
        <&file> KTM_Andi.pdf
        <&file> SPP_Genap.pdf
      } | {
        <b>Linimasa Pelacakan (Timeline)</b>
        --
        <&check> <b>Selesai</b> (14 Mei)
        "Dokumen elektronik telah diterbitkan."
        [ Unduh Dokumen Final ]
        --
        <&check> <b>Disetujui Pejabat</b> (13 Mei)
        "Telah ditandatangani oleh Wakil Dekan."
        --
        <&check> <b>Diproses Staf</b> (12 Mei)
        "Berkas valid, sedang dalam antrian TTE."
        --
        <&check> <b>Menunggu Verifikasi</b> (12 Mei)
        "Permohonan berhasil dikirim."
      }
    }
  }
}
@endsalt""",

    "18_wireframe_admin": """@startsalt
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
          }
        }
      }
    }
  }
}
@endsalt""",

    "19_wireframe_review": """@startsalt
skinparam handwritten true
skinparam monochrome true
{+
  <b>Web Browser</b>
  ==
  { "<&arrow-left>" | "<&arrow-right>" | "<&reload>" | " .../admin/requests/REQ-001 " }
  ==
  {
    {/ <b>ULT FKIP Unila</b> | Notifikasi | Profil | [ Keluar ] }
    --
    {
      {
        <b>Menu</b>
        * Dashboard
        * Antrian
        * Manajemen
      } | {
        <b>Tinjau Permohonan: Surat Aktif Kuliah</b>
        "Pemohon: Andi (2213023001)"
        --
        {
          <b>Pratinjau Data & Lampiran</b>
          Keperluan: Beasiswa
          Lampiran 1: KTM_Andi.pdf [ Buka ]
          Lampiran 2: SPP_Genap.pdf [ Buka ]
        }
        --
        <b>Penomoran Surat & Perakitan</b>
        "Format Nomor (Otomatis):"
        [ 123/UN26.13/KM.01/2026 ]
        "Catatan Verifikasi:"
        [ Berkas lengkap, siap dirakit.       ]
        --
        [ Tolak ] | [ Rakit Dokumen (Assembly) ]
      }
    }
  }
}
@endsalt""",

    "20_wireframe_template": """@startsalt
skinparam handwritten true
skinparam monochrome true
{+
  <b>Web Browser</b>
  ==
  { "<&arrow-left>" | "<&arrow-right>" | "<&reload>" | " .../admin/templates/edit " }
  ==
  {
    {/ <b>ULT FKIP Unila</b> | Notifikasi | Profil | [ Keluar ] }
    --
    {
      {
        <b>Menu</b>
        * Dashboard
        * Antrian
        * Templat
      } | {
        <b>Konfigurasi Layanan & Templat Dokumen</b>
        --
        "Nama Layanan:"
        [ Surat Aktif Kuliah                  ]
        "Deskripsi Layanan:"
        [ Surat untuk keperluan beasiswa...   ]
        --
        <b>Manajemen Variabel Isian (Dynamic Inputs)</b>
        {#
          <b>Nama Field</b> | <b>Tipe Data</b> | <b>Wajib?</b>
          Keperluan | Teks Panjang | [X]
          Nomor HP | Angka | [X]
        }
        [ + Tambah Field Baru ]
        --
        <b>Master Template Word (.docx)</b>
        [ Browse... ] "template_aktif.docx"
        --
        [ Batal ] | [ Simpan Konfigurasi ]
      }
    }
  }
}
@endsalt""",

    "21_wireframe_signer": """@startsalt
skinparam handwritten true
skinparam monochrome true
{+
  <b>Web Browser</b>
  ==
  { "<&arrow-left>" | "<&arrow-right>" | "<&reload>" | " .../signer/inbox " }
  ==
  {
    {/ <b>ULT FKIP Unila</b> | Kotak Masuk TTE | Profil | [ Keluar ] }
    --
    {
      <b>Kotak Masuk Verifikasi Pejabat (Signer Portal)</b>
      "Daftar dokumen yang membutuhkan tanda tangan elektronik Anda."
      --
      {
        <b>Antrian Persetujuan</b>
        {#
          <b>ID</b> | <b>Layanan</b> | <b>Pemohon</b> | <b>Aksi</b>
          REQ-001 | Surat Aktif Kuliah | Andi | [ Tinjau & TTE ]
          REQ-002 | Surat Izin Riset | Budi | [ Tinjau & TTE ]
        }
      }
      --
      <b>Pratinjau Draf Surat (REQ-001)</b>
      {+
        "KEMENTERIAN PENDIDIKAN DAN KEBUDAYAAN..."
        "FAKULTAS KEGURUAN DAN ILMU PENDIDIKAN"
        "..."
        "Menerangkan bahwa mahasiswa di bawah ini:"
        "Nama: Andi"
        "NPM: 2213023001"
        "..."
      }
      [ Tolak Dokumen ] | [ Bubuhkan Tanda Tangan Elektronik ]
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
