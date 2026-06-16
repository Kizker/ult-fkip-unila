<?php

namespace Database\Seeders;

use App\Models\CmsAnnouncement;
use App\Models\CmsBlog;
use App\Models\HeroBanner;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class CmsSeeder extends Seeder
{
    public function run(): void
    {
        // About ULT stored in site_settings
        SiteSetting::updateOrCreate(
            ['key' => 'about_ult_html_id'],
            ['value' => '<p><strong>CONTOH</strong>: ULT FKIP Universitas Lampung melayani administrasi akademik dan kemahasiswaan. Silakan ubah melalui CMS.</p>']
        );
        SiteSetting::updateOrCreate(
            ['key' => 'about_ult_html_en'],
            ['value' => '<p><strong>EXAMPLE</strong>: ULT FKIP Universitas Lampung provides academic and student administration services. Edit via CMS.</p>']
        );

        HeroBanner::updateOrCreate(
            ['id' => 1],
            [
                'title_id' => 'Selamat Datang di ULT FKIP Unila',
                'title_en' => 'Welcome to ULT FKIP Unila',
                'subtitle_id' => 'Pengajuan layanan cepat, transparan, dan terlacak.',
                'subtitle_en' => 'Fast, transparent, and trackable service requests.',
                'image_path' => null,
                'cta_label_id' => 'Lihat Layanan',
                'cta_label_en' => 'Browse Services',
                'cta_url' => '/layanan',
                'is_active' => true,
            ]
        );

        // Cleanup early placeholder samples from initial project bootstrap.
        CmsAnnouncement::query()->where('slug', 'pengumuman-pertama')->delete();
        CmsBlog::query()->where('slug', 'artikel-pertama')->delete();

        $announcements = [
            [
                'slug' => 'penyesuaian-jam-layanan-ult-fkip',
                'title_id' => 'Penyesuaian Jam Layanan ULT FKIP Semester Genap 2025/2026',
                'title_en' => 'ULT FKIP Service Hour Adjustment for Even Semester 2025/2026',
                'content_html_id' => '<p>Mulai Senin, 5 Januari 2026, jam layanan ULT FKIP disesuaikan menjadi pukul 08.00-15.30 WIB pada hari kerja.</p><p>Mahasiswa tetap dapat mengajukan permohonan melalui portal selama 24 jam, sedangkan verifikasi berkas dilakukan pada jam operasional.</p><p>Mohon memastikan dokumen pendukung sudah lengkap agar proses berjalan lebih cepat.</p>',
                'content_html_en' => '<p>Starting Monday, January 5, 2026, ULT FKIP service hours are adjusted to 08.00-15.30 WIB on business days.</p><p>Students can still submit requests through the portal 24/7, while document verification is processed during office hours.</p><p>Please ensure all required documents are complete for faster processing.</p>',
                'published_at' => now()->subDays(20),
            ],
            [
                'slug' => 'maintenance-sistem-pengajuan-layanan',
                'title_id' => 'Pemeliharaan Sistem Pengajuan Layanan pada 12 Januari 2026',
                'title_en' => 'Service Request System Maintenance on January 12, 2026',
                'content_html_id' => '<p>Akan dilakukan pemeliharaan sistem pada Jumat, 12 Januari 2026 pukul 19.00-22.00 WIB.</p><p>Selama periode tersebut, akses login dan pengunggahan berkas mungkin mengalami gangguan sementara.</p><p>Kami sarankan pengguna menyimpan draft dan menghindari pengiriman berkas mendekati jadwal maintenance.</p>',
                'content_html_en' => '<p>System maintenance will be conducted on Friday, January 12, 2026, from 19.00 to 22.00 WIB.</p><p>During this period, login and file upload features may be temporarily unavailable.</p><p>Please save your draft and avoid submitting files close to the maintenance window.</p>',
                'published_at' => now()->subDays(16),
            ],
            [
                'slug' => 'batas-akhir-unggah-berkas-yudisium',
                'title_id' => 'Batas Akhir Unggah Berkas Yudisium Periode Februari 2026',
                'title_en' => 'Deadline for Yudisium Document Upload, February 2026 Period',
                'content_html_id' => '<p>Mahasiswa peserta yudisium periode Februari 2026 diminta mengunggah berkas paling lambat 2 Februari 2026 pukul 23.59 WIB.</p><p>Berkas yang belum lengkap hingga batas waktu akan diproses pada periode berikutnya.</p><p>Silakan cek kembali format dokumen pada panduan layanan sebelum mengirim permohonan.</p>',
                'content_html_en' => '<p>Students participating in the February 2026 yudisium period must upload all required documents no later than February 2, 2026 at 23.59 WIB.</p><p>Incomplete submissions after the deadline will be processed in the next period.</p><p>Please review the document format guideline before submitting your request.</p>',
                'published_at' => now()->subDays(12),
            ],
            [
                'slug' => 'informasi-layanan-selama-cuti-bersama',
                'title_id' => 'Informasi Layanan ULT Selama Cuti Bersama Nasional',
                'title_en' => 'ULT Service Information During National Joint Leave',
                'content_html_id' => '<p>Selama cuti bersama nasional tanggal 8-9 Februari 2026, layanan verifikasi manual tidak beroperasi.</p><p>Portal pengajuan tetap dapat diakses untuk membuat permohonan baru dan memantau status layanan.</p><p>Proses verifikasi akan kembali normal pada hari kerja berikutnya.</p>',
                'content_html_en' => '<p>During the national joint leave on February 8-9, 2026, manual verification services will be unavailable.</p><p>The service portal remains accessible for new submissions and status tracking.</p><p>Verification will resume on the next business day.</p>',
                'published_at' => now()->subDays(4),
            ],
        ];

        foreach ($announcements as $item) {
            CmsAnnouncement::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'title_id' => $item['title_id'],
                    'title_en' => $item['title_en'],
                    'content_html_id' => $item['content_html_id'],
                    'content_html_en' => $item['content_html_en'],
                    'published_at' => $item['published_at'],
                    'is_published' => true,
                ]
            );
        }

        $blogs = [
            [
                'slug' => 'tips-mengunggah-berkas-akademik',
                'title_id' => '7 Tips Mengunggah Berkas Akademik agar Cepat Diverifikasi',
                'title_en' => '7 Tips for Uploading Academic Files to Speed Up Verification',
                'content_html_id' => '<p>Berkas yang rapi mempercepat proses verifikasi admin.</p><p>Gunakan nama file yang jelas, satukan halaman terkait dalam satu PDF, pastikan tanda tangan terbaca, dan hindari foto blur.</p><p>Selalu cek ukuran file sesuai batas maksimal sebelum menekan tombol kirim.</p>',
                'content_html_en' => '<p>Well-prepared files speed up the admin verification process.</p><p>Use clear file names, merge related pages into one PDF, make signatures readable, and avoid blurry photos.</p><p>Always check file size limits before submitting.</p>',
                'published_at' => now()->subDays(15),
            ],
            [
                'slug' => 'memahami-alur-tanda-tangan-digital',
                'title_id' => 'Memahami Alur Tanda Tangan Digital pada Layanan Dokumen',
                'title_en' => 'Understanding the Digital Signature Flow in Document Services',
                'content_html_id' => '<p>Pada beberapa layanan, dokumen diproses melalui tahapan verifikasi, persetujuan, hingga tanda tangan digital pejabat berwenang.</p><p>Setiap tahap memiliki waktu proses berbeda tergantung kelengkapan berkas dan antrean.</p><p>Mahasiswa dapat melihat progres secara transparan melalui timeline status.</p>',
                'content_html_en' => '<p>In selected services, documents go through verification, approval, and authorized digital signature stages.</p><p>Each stage may have different processing times depending on document completeness and queue load.</p><p>Students can track progress transparently through the status timeline.</p>',
                'published_at' => now()->subDays(11),
            ],
            [
                'slug' => 'faq-surat-keterangan-aktif-kuliah',
                'title_id' => 'FAQ Surat Keterangan Aktif Kuliah: Pertanyaan yang Paling Sering Muncul',
                'title_en' => 'FAQ on Active Student Certificate: Most Frequently Asked Questions',
                'content_html_id' => '<p>Permohonan surat keterangan aktif kuliah sering diajukan untuk beasiswa, administrasi bank, dan kebutuhan instansi.</p><p>Pertanyaan umum meliputi format berkas, lama proses, serta cara revisi jika data kurang sesuai.</p><p>Pastikan NPM dan program studi ditulis sesuai data akademik resmi.</p>',
                'content_html_en' => '<p>Active student certificate requests are commonly needed for scholarships, banking administration, and institutional requirements.</p><p>Common questions include file format, processing duration, and revision steps when data is incorrect.</p><p>Ensure your student ID and study program match official academic records.</p>',
                'published_at' => now()->subDays(7),
            ],
            [
                'slug' => 'cara-melacak-status-permohonan',
                'title_id' => 'Cara Melacak Status Permohonan di Portal ULT Secara Efektif',
                'title_en' => 'How to Track Your Request Status Effectively in the ULT Portal',
                'content_html_id' => '<p>Setelah mengirim permohonan, gunakan menu riwayat untuk melihat posisi dokumen terbaru.</p><p>Perhatikan label status seperti Menunggu Verifikasi, Perlu Revisi, atau Selesai agar dapat menindaklanjuti lebih cepat.</p><p>Aktifkan notifikasi email untuk mendapatkan pembaruan proses secara otomatis.</p>',
                'content_html_en' => '<p>After submission, use the history menu to see your latest document status.</p><p>Watch status labels such as Waiting for Verification, Revision Needed, or Completed to respond quickly.</p><p>Enable email notifications to receive automatic process updates.</p>',
                'published_at' => now()->subDays(3),
            ],
        ];

        foreach ($blogs as $item) {
            CmsBlog::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'title_id' => $item['title_id'],
                    'title_en' => $item['title_en'],
                    'content_html_id' => $item['content_html_id'],
                    'content_html_en' => $item['content_html_en'],
                    'published_at' => $item['published_at'],
                    'is_published' => true,
                ]
            );
        }
    }
}
