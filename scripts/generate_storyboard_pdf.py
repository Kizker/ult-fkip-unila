from __future__ import annotations

from pathlib import Path
from textwrap import wrap

from PIL import Image, ImageDraw, ImageFont, ImageOps


ROOT = Path(__file__).resolve().parents[1]
SCREENSHOT_DIR = ROOT / "docs" / "buku-panduan" / "assets" / "screenshots"
OUTPUT_DIR = ROOT / "docs" / "video tutorial" / "storyboard visual"
IMAGES_DIR = OUTPUT_DIR / "images"
PDF_PATH = OUTPUT_DIR / "storyboard-pemohon.pdf"

PAGE_SIZE = (1600, 900)
MARGIN = 48
HEADER_H = 120


def font(size: int, bold: bool = False) -> ImageFont.FreeTypeFont | ImageFont.ImageFont:
    candidates = []
    if bold:
        candidates.extend(
            [
                r"C:\Windows\Fonts\arialbd.ttf",
                r"C:\Windows\Fonts\segoeuib.ttf",
            ]
        )
    else:
        candidates.extend(
            [
                r"C:\Windows\Fonts\arial.ttf",
                r"C:\Windows\Fonts\segoeui.ttf",
            ]
        )
    for item in candidates:
        path = Path(item)
        if path.exists():
            return ImageFont.truetype(str(path), size=size)
    return ImageFont.load_default()


TITLE_FONT = font(42, bold=True)
SUBTITLE_FONT = font(24, bold=False)
SECTION_FONT = font(24, bold=True)
BODY_FONT = font(18, bold=False)
SMALL_FONT = font(15, bold=False)
BIG_FONT = font(58, bold=True)


VIDEOS = [
    {
        "video_no": 1,
        "video_title": "Pengenalan Sistem Layanan ULT FKIP Unila",
        "scenes": [
            {"scene_no": 1, "scene_title": "Opening title", "visual": "Judul video tampil sebagai pembuka sebelum masuk ke halaman sistem.", "narration": "Selamat datang pada video tutorial Sistem Layanan ULT FKIP Unila.", "screenshot": "01-beranda-website-ult.png"},
            {"scene_no": 2, "scene_title": "Tampilan halaman utama", "visual": "Halaman beranda atau halaman publik sistem ditampilkan penuh.", "narration": "Video ini akan memberikan penjelasan awal mengenai fungsi sistem layanan, jenis layanan yang tersedia, serta alur umum penggunaan sistem bagi mahasiswa atau pemohon layanan.", "screenshot": "01-beranda-website-ult.png"},
            {"scene_no": 3, "scene_title": "Sorot fungsi utama sistem", "visual": "Kursor mengarah ke area layanan, informasi, atau menu publik.", "narration": "Sistem layanan ini disediakan untuk memudahkan proses pengajuan layanan administrasi secara daring.", "screenshot": "02-menu-navigasi-utama.png"},
            {"scene_no": 4, "scene_title": "Sorot manfaat sistem", "visual": "Highlight pada bagian layanan, panduan, atau informasi proses.", "narration": "Dengan adanya sistem ini, pengguna dapat mengakses informasi layanan, mengajukan permohonan, memantau status proses, melakukan perbaikan apabila diperlukan, serta mengunduh hasil layanan melalui satu sistem yang terintegrasi.", "screenshot": "08-panduan-pengguna-publik.png"},
            {"scene_no": 5, "scene_title": "Sorot daftar layanan", "visual": "Daftar layanan yang tersedia ditampilkan agar pengguna memahami pilihan layanan.", "narration": "Melalui halaman utama sistem, pengguna dapat melihat berbagai layanan yang tersedia.", "screenshot": "03-daftar-layanan.png"},
            {"scene_no": 6, "scene_title": "Sorot informasi layanan", "visual": "Halaman detail layanan dibuka dan bagian persyaratan diperlihatkan.", "narration": "Setiap layanan umumnya dilengkapi dengan informasi penting, seperti deskripsi layanan, persyaratan, serta petunjuk yang perlu dipahami sebelum melakukan pengajuan.", "screenshot": "06-persyaratan-dan-sop-layanan.png"},
            {"scene_no": 7, "scene_title": "Sorot tombol daftar dan masuk", "visual": "Kursor berpindah ke tombol daftar dan login.", "narration": "Secara umum, alur penggunaan sistem dimulai dari pembuatan akun, kemudian login ke dalam sistem.", "screenshot": "07-halaman-login.png"},
            {"scene_no": 8, "scene_title": "Penutup video", "visual": "Tampilan awal sistem ditutup dengan ajakan masuk ke langkah berikutnya.", "narration": "Pada video berikutnya, akan dijelaskan cara daftar akun, login ke sistem, dan melengkapi profil pengguna.", "screenshot": "01-register-akun-pemohon.png"},
        ],
    },
    {
        "video_no": 2,
        "video_title": "Cara Daftar, Login, dan Melengkapi Profil",
        "scenes": [
            {"scene_no": 1, "scene_title": "Opening title", "visual": "Judul video tampil sebagai pembuka.", "narration": "Pada video ini akan dijelaskan langkah-langkah pendaftaran akun, proses login, serta cara melengkapi profil pengguna.", "screenshot": "01-register-akun-pemohon.png"},
            {"scene_no": 2, "scene_title": "Halaman registrasi", "visual": "Form pendaftaran akun dibuka.", "narration": "Langkah pertama adalah membuka halaman pendaftaran akun.", "screenshot": "01-register-akun-pemohon.png"},
            {"scene_no": 3, "scene_title": "Pengisian data registrasi", "visual": "Kursor mengisi nama, email, NIM, dan kolom lain.", "narration": "Pada halaman ini, pengguna diminta mengisi data identitas sesuai informasi yang sebenarnya.", "screenshot": "01-register-akun-pemohon.png"},
            {"scene_no": 4, "scene_title": "Sorot ketelitian pengisian", "visual": "Zoom pada kolom form penting agar pengguna teliti saat mengisi data.", "narration": "Isilah nama lengkap, alamat email, nomor induk mahasiswa, dan data lain yang diminta oleh sistem secara benar dan teliti.", "screenshot": "01-register-akun-pemohon.png"},
            {"scene_no": 5, "scene_title": "Pembuatan password", "visual": "Kursor mengisi password pada form pendaftaran.", "narration": "Setelah seluruh data terisi, buatlah password untuk akun yang akan digunakan.", "screenshot": "01-register-akun-pemohon.png"},
            {"scene_no": 6, "scene_title": "Kirim formulir pendaftaran", "visual": "Tombol daftar atau registrasi diklik.", "narration": "Jika seluruh data telah benar, klik tombol daftar atau registrasi untuk mengirimkan pendaftaran akun.", "screenshot": "01-register-akun-pemohon.png"},
            {"scene_no": 7, "scene_title": "Halaman login", "visual": "Tampilan form login setelah proses registrasi selesai.", "narration": "Setelah proses registrasi berhasil, pengguna dapat melanjutkan ke halaman login.", "screenshot": "02-halaman-login-pemohon.png"},
            {"scene_no": 8, "scene_title": "Proses login", "visual": "Email dan password diisi lalu tombol masuk ditekan.", "narration": "Masukkan email atau akun yang telah didaftarkan, kemudian masukkan password yang sesuai.", "screenshot": "02-halaman-login-pemohon.png"},
            {"scene_no": 9, "scene_title": "Dashboard pemohon", "visual": "Dashboard pemohon muncul setelah login berhasil.", "narration": "Apabila data login benar, pengguna akan diarahkan ke dashboard pemohon.", "screenshot": "03-dashboard-pemohon.png"},
            {"scene_no": 10, "scene_title": "Menu profil", "visual": "Pengguna diarahkan membuka menu profil atau pengaturan akun.", "narration": "Sebelum mengajukan layanan, pengguna disarankan untuk membuka menu profil dan memeriksa kelengkapan data diri terlebih dahulu.", "screenshot": "04-ringkasan-dashboard-pemohon.png"},
            {"scene_no": 11, "scene_title": "Pemeriksaan data profil", "visual": "Bagian data pribadi dan data akademik diperlihatkan untuk dicek.", "narration": "Pastikan data pribadi dan data akademik telah terisi dengan benar.", "screenshot": "09-dashboard-pemohon.png"},
            {"scene_no": 12, "scene_title": "Unggah dokumen pendukung", "visual": "Area unggah file ditunjukkan jika tersedia.", "narration": "Dalam kondisi tertentu, sistem juga dapat meminta pengguna untuk mengunggah dokumen pendukung.", "screenshot": "12-bagian-upload-dokumen.png"},
            {"scene_no": 13, "scene_title": "Simpan perubahan", "visual": "Perubahan profil disimpan setelah data dilengkapi.", "narration": "Setelah seluruh data profil diperiksa dan dilengkapi, simpan perubahan yang telah dilakukan.", "screenshot": "09-dashboard-pemohon.png"},
            {"scene_no": 14, "scene_title": "Penutup", "visual": "Profil berada pada kondisi lengkap dan siap dipakai.", "narration": "Setelah proses pendaftaran, login, dan pelengkapan profil selesai, pengguna telah siap untuk mengajukan layanan melalui sistem.", "screenshot": "03-dashboard-pemohon.png"},
        ],
    },
    {
        "video_no": 3,
        "video_title": "Cara Mengajukan Layanan Surat dan Sertifikat",
        "scenes": [
            {"scene_no": 1, "scene_title": "Opening title", "visual": "Judul video tampil sebagai pembuka bagian pengajuan layanan.", "narration": "Pada video ini akan dijelaskan cara mengajukan layanan melalui Sistem Layanan ULT FKIP Unila.", "screenshot": "03-daftar-layanan.png"},
            {"scene_no": 2, "scene_title": "Masuk ke menu layanan", "visual": "Dari dashboard, pengguna membuka menu layanan atau daftar layanan.", "narration": "Langkah pertama adalah membuka menu layanan atau daftar layanan yang tersedia.", "screenshot": "03-daftar-layanan.png"},
            {"scene_no": 3, "scene_title": "Daftar layanan", "visual": "Beberapa jenis layanan terlihat di layar.", "narration": "Pada halaman ini, pengguna dapat melihat berbagai jenis layanan yang dapat diajukan.", "screenshot": "03-daftar-layanan.png"},
            {"scene_no": 4, "scene_title": "Pilih layanan", "visual": "Salah satu layanan dipilih sesuai kebutuhan pengguna.", "narration": "Pilih layanan yang sesuai dengan kebutuhan.", "screenshot": "05-detail-layanan.png"},
            {"scene_no": 5, "scene_title": "Baca informasi layanan", "visual": "Detail layanan dan bagian persyaratan disorot sebelum pengajuan dimulai.", "narration": "Bacalah terlebih dahulu informasi layanan, deskripsi, persyaratan, serta petunjuk yang tersedia.", "screenshot": "06-persyaratan-dan-sop-layanan.png"},
            {"scene_no": 6, "scene_title": "Buka formulir pengajuan", "visual": "Tombol ajukan layanan diklik untuk membuka form.", "narration": "Setelah memahami informasi layanan, klik tombol ajukan layanan untuk membuka formulir permohonan.", "screenshot": "09-form-pengajuan-layanan-biasa.png"},
            {"scene_no": 7, "scene_title": "Isi formulir", "visual": "Kolom-kolom utama pada formulir diisi secara lengkap.", "narration": "Pada formulir tersebut, isi seluruh kolom yang diminta secara lengkap dan teliti.", "screenshot": "11-form-pengajuan-layanan.png"},
            {"scene_no": 8, "scene_title": "Contoh layanan surat", "visual": "Pengisian form layanan surat diperlihatkan.", "narration": "Untuk layanan surat, pengguna umumnya diminta mengisi informasi yang berkaitan dengan kebutuhan surat, data pemohon, dan dokumen pendukung yang relevan.", "screenshot": "09-form-pengajuan-layanan-biasa.png"},
            {"scene_no": 9, "scene_title": "Contoh layanan sertifikat", "visual": "Pengisian bagian layanan sertifikat atau data kegiatan diperlihatkan.", "narration": "Untuk layanan sertifikat, pengguna perlu memastikan bahwa nama, identitas, data kegiatan, dan informasi pendukung lain diisi dengan benar.", "screenshot": "12-form-layanan-sertifikat-piagam.png"},
            {"scene_no": 10, "scene_title": "Unggah lampiran", "visual": "Dokumen pendukung dipilih dan diunggah melalui form.", "narration": "Jika terdapat bagian unggah dokumen, pilih file lampiran yang sesuai dengan ketentuan.", "screenshot": "12-bagian-upload-dokumen.png"},
            {"scene_no": 11, "scene_title": "Pemeriksaan akhir", "visual": "Pengguna meninjau ulang form dan lampiran sebelum mengirim.", "narration": "Setelah seluruh data diisi dan dokumen pendukung berhasil diunggah, periksa kembali seluruh informasi pada formulir pengajuan.", "screenshot": "15-detail-permohonan-setelah-submit.png"},
            {"scene_no": 12, "scene_title": "Kirim permohonan", "visual": "Tombol kirim atau submit ditekan.", "narration": "Apabila seluruh data telah benar, klik tombol kirim, submit, atau ajukan untuk mengirimkan permohonan ke sistem.", "screenshot": "15-detail-permohonan-setelah-submit.png"},
            {"scene_no": 13, "scene_title": "Notifikasi berhasil", "visual": "Sistem menampilkan hasil pengajuan berhasil atau detail permohonan baru.", "narration": "Jika pengajuan berhasil, sistem umumnya akan menampilkan notifikasi bahwa permohonan telah berhasil dibuat.", "screenshot": "15-detail-permohonan-setelah-submit.png"},
            {"scene_no": 14, "scene_title": "Penutup", "visual": "Permohonan terlihat masuk ke daftar riwayat pengguna.", "narration": "Pada tahap ini, proses pengajuan telah selesai dilakukan dan pengguna tinggal menunggu verifikasi atau tindak lanjut dari pengelola layanan.", "screenshot": "15-riwayat-permohonan.png"},
        ],
    },
    {
        "video_no": 4,
        "video_title": "Cara Cek Status, Revisi, dan Unduh Hasil Layanan",
        "scenes": [
            {"scene_no": 1, "scene_title": "Opening title", "visual": "Judul video tampil sebagai pembuka tahap pemantauan hasil layanan.", "narration": "Pada video ini akan dijelaskan cara memantau status permohonan, melakukan revisi apabila diperlukan, dan mengunduh hasil layanan melalui sistem.", "screenshot": "15-riwayat-permohonan.png"},
            {"scene_no": 2, "scene_title": "Buka riwayat permohonan", "visual": "Pengguna membuka menu daftar permohonan atau riwayat permohonan dari dashboard.", "narration": "Setelah pengguna mengirimkan permohonan, langkah selanjutnya adalah membuka menu daftar permohonan atau riwayat permohonan.", "screenshot": "15-riwayat-permohonan.png"},
            {"scene_no": 3, "scene_title": "Daftar pengajuan", "visual": "Semua pengajuan dan status terkininya terlihat pada layar.", "narration": "Pada halaman ini, pengguna dapat melihat seluruh pengajuan yang pernah dibuat beserta status terkininya.", "screenshot": "16-status-dan-riwayat-permohonan.png"},
            {"scene_no": 4, "scene_title": "Buka detail permohonan", "visual": "Salah satu permohonan dibuka untuk melihat rincian proses.", "narration": "Pilih salah satu permohonan untuk melihat detail proses layanan.", "screenshot": "13-detail-permohonan-pemohon.png"},
            {"scene_no": 5, "scene_title": "Status diproses", "visual": "Badge atau indikator status diproses diperlihatkan.", "narration": "Apabila status menunjukkan bahwa permohonan sedang diproses, artinya pengajuan telah diterima dan sedang ditangani oleh petugas atau pengelola layanan.", "screenshot": "16-status-permohonan.png"},
            {"scene_no": 6, "scene_title": "Status revisi", "visual": "Contoh permohonan dengan status revisi diperlihatkan.", "narration": "Apabila status menunjukkan revisi atau perbaikan, bacalah catatan yang diberikan dengan saksama.", "screenshot": "17-perbaikan-permohonan.png"},
            {"scene_no": 7, "scene_title": "Catatan revisi", "visual": "Catatan perbaikan dari petugas diperbesar agar jelas.", "narration": "Catatan tersebut berisi informasi mengenai data atau dokumen yang perlu diperbaiki oleh pengguna.", "screenshot": "17-form-perbaikan-permohonan.png"},
            {"scene_no": 8, "scene_title": "Lakukan perbaikan", "visual": "Form dibuka kembali dan data atau lampiran diperbaiki.", "narration": "Setelah memahami arahan revisi, buka kembali permohonan terkait dan lakukan perbaikan pada bagian yang diminta.", "screenshot": "17-form-perbaikan-permohonan.png"},
            {"scene_no": 9, "scene_title": "Kirim ulang", "visual": "Permohonan revisi dikirim ulang setelah diperbaiki.", "narration": "Setelah seluruh perbaikan selesai dilakukan, periksa kembali isi permohonan, lalu kirim ulang sesuai mekanisme yang tersedia pada sistem.", "screenshot": "17-perbaikan-permohonan.png"},
            {"scene_no": 10, "scene_title": "Status selesai", "visual": "Contoh permohonan yang sudah selesai diproses diperlihatkan.", "narration": "Jika status permohonan menunjukkan bahwa layanan telah selesai, pengguna umumnya dapat mengunduh hasil layanan melalui halaman detail permohonan.", "screenshot": "16-hasil-layanan-download-output.png"},
            {"scene_no": 11, "scene_title": "Unduh hasil", "visual": "Tombol unduh atau download diklik untuk mengambil dokumen hasil layanan.", "narration": "Klik tombol unduh atau download untuk mengambil dokumen hasil layanan.", "screenshot": "20-output-layanan-unduh-berkas.png"},
            {"scene_no": 12, "scene_title": "Cek dokumen", "visual": "File hasil layanan dibuka atau terlihat di folder unduhan.", "narration": "Setelah dokumen berhasil diunduh, periksa kembali isi dokumen untuk memastikan bahwa informasi utama telah sesuai.", "screenshot": "19-preview-dokumen-permohonan.png"},
            {"scene_no": 13, "scene_title": "Penutup", "visual": "Kembali ke riwayat permohonan sebagai penutup alur layanan.", "narration": "Melalui fitur riwayat permohonan, pengguna dapat memantau seluruh proses layanan secara lebih mudah dan terstruktur.", "screenshot": "20-riwayat-status-permohonan.png"},
        ],
    },
]


def ensure_dirs() -> None:
    IMAGES_DIR.mkdir(parents=True, exist_ok=True)


def fit_image(img: Image.Image, width: int, height: int) -> Image.Image:
    return ImageOps.contain(img.convert("RGB"), (width, height))


def draw_wrapped_text(draw: ImageDraw.ImageDraw, text: str, xy: tuple[int, int], max_width: int, line_height: int, fill: str, text_font: ImageFont.ImageFont) -> int:
    x, y = xy
    avg_char_width = max(8, text_font.size // 2) if hasattr(text_font, "size") else 10
    wrap_width = max(20, max_width // avg_char_width)
    lines: list[str] = []
    for paragraph in text.splitlines():
        if not paragraph.strip():
            lines.append("")
            continue
        lines.extend(wrap(paragraph, width=wrap_width))
    for line in lines:
        draw.text((x, y), line, font=text_font, fill=fill)
        y += line_height
    return y


def draw_page_number(draw: ImageDraw.ImageDraw, page_no: int, total_pages: int) -> None:
    label = f"Halaman {page_no} / {total_pages}"
    bbox = draw.textbbox((0, 0), label, font=SMALL_FONT)
    width = bbox[2] - bbox[0]
    draw.text((PAGE_SIZE[0] - MARGIN - width, PAGE_SIZE[1] - 36), label, font=SMALL_FONT, fill="#6b7280")


def render_cover(total_scenes: int, total_pages: int) -> Path:
    img = Image.new("RGB", PAGE_SIZE, "#0f2747")
    draw = ImageDraw.Draw(img)
    draw.rounded_rectangle((40, 40, PAGE_SIZE[0] - 40, PAGE_SIZE[1] - 40), radius=36, outline="#93b4e6", width=2)
    draw.text((90, 120), "Storyboard Visual", font=BIG_FONT, fill="#ffffff")
    draw.text((90, 205), "Tutorial Pemohon Layanan", font=TITLE_FONT, fill="#dce8ff")
    draw.text((90, 265), "ULT FKIP Unila", font=TITLE_FONT, fill="#dce8ff")

    stats = [
        f"Jumlah video: {len(VIDEOS)}",
        f"Jumlah scene: {total_scenes}",
        "Format: gambar per scene + PDF",
        "Sumber visual: screenshot lokal buku panduan",
    ]
    y = 380
    for item in stats:
        draw.text((110, y), f"- {item}", font=SUBTITLE_FONT, fill="#e8eefc")
        y += 42

    note = (
        "Dokumen ini disusun untuk membantu proses rekaman, review alur, "
        "dan koordinasi antara penulis naskah, pengisi suara, serta editor video."
    )
    draw_wrapped_text(draw, note, (90, 600), 1120, 34, "#cbd9f5", SUBTITLE_FONT)
    draw_page_number(draw, 1, total_pages)

    path = IMAGES_DIR / "00-cover.png"
    img.save(path)
    return path


def render_video_summary(video: dict, page_no: int, total_pages: int) -> Path:
    img = Image.new("RGB", PAGE_SIZE, "#f4f6fb")
    draw = ImageDraw.Draw(img)
    draw.rounded_rectangle((24, 24, PAGE_SIZE[0] - 24, PAGE_SIZE[1] - 24), radius=28, fill="#ffffff", outline="#d7deea", width=2)
    draw.rounded_rectangle((24, 24, PAGE_SIZE[0] - 24, 180), radius=28, fill="#143a6f")
    draw.rectangle((24, 152, PAGE_SIZE[0] - 24, 180), fill="#143a6f")

    draw.text((MARGIN, 56), f"Ringkasan Video {video['video_no']}", font=SUBTITLE_FONT, fill="#d7e6ff")
    draw.text((MARGIN, 86), video["video_title"], font=TITLE_FONT, fill="#ffffff")

    left_box = (MARGIN, 220, 740, 760)
    right_box = (790, 220, PAGE_SIZE[0] - MARGIN, 760)
    draw.rounded_rectangle(left_box, radius=20, fill="#f9fbff", outline="#d4dceb", width=2)
    draw.rounded_rectangle(right_box, radius=20, fill="#f9fbff", outline="#d4dceb", width=2)

    draw.text((left_box[0] + 20, left_box[1] + 20), "Isi Video", font=SECTION_FONT, fill="#143a6f")
    bullets = [
        f"Jumlah scene: {len(video['scenes'])}",
        "Format setiap scene: referensi visual, deskripsi visual, dan narasi.",
        "Gunakan halaman ini untuk memahami alur sebelum masuk ke scene detail.",
    ]
    y = left_box[1] + 70
    for bullet in bullets:
        y = draw_wrapped_text(draw, f"- {bullet}", (left_box[0] + 24, y), left_box[2] - left_box[0] - 48, 30, "#374151", BODY_FONT)
        y += 8

    draw.text((right_box[0] + 20, right_box[1] + 20), "Daftar Scene", font=SECTION_FONT, fill="#143a6f")
    y = right_box[1] + 68
    for scene in video["scenes"]:
        y = draw_wrapped_text(draw, f"{scene['scene_no']:02d}. {scene['scene_title']}", (right_box[0] + 24, y), right_box[2] - right_box[0] - 48, 26, "#374151", BODY_FONT)
        y += 6

    draw.text((MARGIN, PAGE_SIZE[1] - 36), "Storyboard pemohon layanan", font=SMALL_FONT, fill="#6b7280")
    draw_page_number(draw, page_no, total_pages)
    path = IMAGES_DIR / f"video-{video['video_no']:02d}-summary.png"
    img.save(path)
    return path


def render_scene(video_no: int, video_title: str, scene: dict, page_no: int, total_pages: int) -> Path:
    img = Image.new("RGB", PAGE_SIZE, "#f4f6fb")
    draw = ImageDraw.Draw(img)

    draw.rounded_rectangle((24, 24, PAGE_SIZE[0] - 24, PAGE_SIZE[1] - 24), radius=28, fill="#ffffff", outline="#d7deea", width=2)
    draw.rounded_rectangle((24, 24, PAGE_SIZE[0] - 24, HEADER_H), radius=28, fill="#143a6f")
    draw.rectangle((24, HEADER_H - 28, PAGE_SIZE[0] - 24, HEADER_H), fill="#143a6f")

    draw.text((MARGIN, 44), f"Video {video_no}", font=SUBTITLE_FONT, fill="#d7e6ff")
    draw.text((MARGIN, 70), video_title, font=TITLE_FONT, fill="#ffffff")
    draw.text((PAGE_SIZE[0] - 300, 58), f"Scene {scene['scene_no']:02d}", font=TITLE_FONT, fill="#ffffff")

    left = MARGIN
    right = PAGE_SIZE[0] - MARGIN
    image_box = (left, 150, 980, 690)
    notes_box = (1010, 150, right, 690)
    footer_box = (left, 712, right, PAGE_SIZE[1] - 56)

    draw.rounded_rectangle(image_box, radius=20, fill="#eef3fb", outline="#d4dceb", width=2)
    draw.rounded_rectangle(notes_box, radius=20, fill="#f9fbff", outline="#d4dceb", width=2)
    draw.rounded_rectangle(footer_box, radius=20, fill="#f9fbff", outline="#d4dceb", width=2)

    screenshot_path = SCREENSHOT_DIR / scene["screenshot"]
    if screenshot_path.exists():
        shot = Image.open(screenshot_path)
        fitted = fit_image(shot, image_box[2] - image_box[0] - 24, image_box[3] - image_box[1] - 56)
        paste_x = image_box[0] + ((image_box[2] - image_box[0]) - fitted.width) // 2
        paste_y = image_box[1] + 18 + ((image_box[3] - image_box[1] - 56) - fitted.height) // 2
        img.paste(fitted, (paste_x, paste_y))
    else:
        draw.text((image_box[0] + 24, image_box[1] + 30), "Screenshot tidak ditemukan", font=SECTION_FONT, fill="#6b7280")

    draw.text((image_box[0] + 20, image_box[3] - 32), f"Referensi visual: {scene['screenshot']}", font=SMALL_FONT, fill="#4b5563")

    notes_y = notes_box[1] + 20
    draw.text((notes_box[0] + 20, notes_y), scene["scene_title"], font=SECTION_FONT, fill="#143a6f")
    notes_y += 42
    draw.text((notes_box[0] + 20, notes_y), "Visual", font=SECTION_FONT, fill="#1f2937")
    notes_y += 34
    notes_y = draw_wrapped_text(draw, scene["visual"], (notes_box[0] + 20, notes_y), notes_box[2] - notes_box[0] - 40, 26, "#374151", BODY_FONT)
    notes_y += 16
    draw.text((notes_box[0] + 20, notes_y), "Narasi", font=SECTION_FONT, fill="#1f2937")
    notes_y += 34
    draw_wrapped_text(draw, scene["narration"], (notes_box[0] + 20, notes_y), notes_box[2] - notes_box[0] - 40, 26, "#374151", BODY_FONT)

    footer_y = footer_box[1] + 18
    draw.text((footer_box[0] + 20, footer_y), "Catatan Produksi", font=SECTION_FONT, fill="#143a6f")
    footer_y += 34
    production_notes = "Gunakan gerakan mouse yang jelas, beri jeda seperlunya, dan tampilkan teks layar singkat sesuai langkah pada scene ini."
    draw_wrapped_text(draw, production_notes, (footer_box[0] + 20, footer_y), footer_box[2] - footer_box[0] - 40, 24, "#374151", BODY_FONT)
    draw.text((MARGIN, PAGE_SIZE[1] - 36), "Storyboard pemohon layanan - generated from local screenshots", font=SMALL_FONT, fill="#6b7280")
    draw_page_number(draw, page_no, total_pages)

    output_path = IMAGES_DIR / f"video-{video_no:02d}-scene-{scene['scene_no']:02d}.png"
    img.save(output_path)
    return output_path


def save_pdf(path: Path, image_paths: list[Path]) -> None:
    images = [Image.open(item).convert("RGB") for item in image_paths]
    first, rest = images[0], images[1:]
    first.save(path, save_all=True, append_images=rest)


def main() -> None:
    ensure_dirs()
    total_scenes = sum(len(video["scenes"]) for video in VIDEOS)
    total_pages = 1 + len(VIDEOS) + total_scenes

    all_pages: list[Path] = []
    all_pages.append(render_cover(total_scenes, total_pages))

    page_no = 2
    for video in VIDEOS:
        summary = render_video_summary(video, page_no, total_pages)
        all_pages.append(summary)
        page_no += 1

        video_pages = [summary]
        for scene in video["scenes"]:
            scene_path = render_scene(video["video_no"], video["video_title"], scene, page_no, total_pages)
            all_pages.append(scene_path)
            video_pages.append(scene_path)
            page_no += 1

        video_pdf = OUTPUT_DIR / f"storyboard-video-{video['video_no']:02d}.pdf"
        save_pdf(video_pdf, video_pages)

    save_pdf(PDF_PATH, all_pages)
    print(f"Generated {total_scenes} scene images")
    print(f"Generated combined PDF: {PDF_PATH}")
    for video in VIDEOS:
        video_pdf = OUTPUT_DIR / f"storyboard-video-{video['video_no']:02d}.pdf"
        print(f"Generated per-video PDF: {video_pdf}")


if __name__ == "__main__":
    main()
