<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

/**
 * Generate deterministic dummy .docx files (macro-free) for local upload testing.
 *
 * Output:
 * - storage/app/seed/dummy-docx/   (private seed files)
 * - public/demo-files/            (DEV ONLY; never store sensitive data here)
 */
class GenerateDummyDocx extends Command
{
    protected $signature = 'ult:generate-dummy-docx {--force : Overwrite existing dummy files}';

    protected $description = 'Generate 5 dummy DOCX files (CONTOH/DUMMY) for ULT FKIP upload testing.';

    public function handle(): int
    {
        $seedDir = storage_path('app/seed/dummy-docx');
        $publicDir = public_path('demo-files');

        File::ensureDirectoryExists($seedDir);
        File::ensureDirectoryExists($publicDir);

        $files = [
            [
                'name' => 'dummy-surat-permohonan-01.docx',
                'title' => 'CONTOH / DUMMY — Surat Permohonan Layanan ULT FKIP Universitas Lampung',
                'lines' => [
                    'Nama: [CONTOH]',
                    'NPM: [CONTOH]',
                    'Program Studi: [CONTOH]',
                    'Jenis Layanan: [CONTOH]',
                    'Keterangan: Dokumen ini hanya untuk uji coba upload pada Web ULT FKIP.',
                ],
            ],
            [
                'name' => 'dummy-surat-keterangan-02.docx',
                'title' => 'CONTOH / DUMMY — Surat Keterangan (Template ULT FKIP)',
                'lines' => [
                    'Nomor: [CONTOH]',
                    'Yang bertanda tangan di bawah ini menyatakan bahwa dokumen ini adalah contoh.',
                    'Keperluan: Uji coba sistem Web ULT FKIP Universitas Lampung.',
                ],
            ],
            [
                'name' => 'dummy-form-pengajuan-03.docx',
                'title' => 'CONTOH / DUMMY — Form Pengajuan Dokumen',
                'lines' => [
                    'Daftar dokumen pendukung (contoh):',
                    '1) Proposal [CONTOH]',
                    '2) Surat pengantar [CONTOH]',
                    'Catatan: Ini hanya dummy untuk pengujian lampiran.',
                ],
            ],
            [
                'name' => 'dummy-pernyataan-keabsahan-04.docx',
                'title' => 'CONTOH / DUMMY — Pernyataan Keabsahan Dokumen',
                'lines' => [
                    'Saya menyatakan dokumen yang saya unggah adalah benar dan dapat dipertanggungjawabkan.',
                    'Tanda tangan: [CONTOH]',
                ],
            ],
            [
                'name' => 'dummy-lampiran-pendukung-05.docx',
                'title' => 'CONTOH / DUMMY — Lampiran Pendukung',
                'lines' => [
                    'Lampiran pendukung (contoh):',
                    '1) KTP [CONTOH]',
                    '2) KTM [CONTOH]',
                    '3) Bukti lainnya [CONTOH]',
                ],
            ],
        ];

        $force = (bool) $this->option('force');

        foreach ($files as $f) {
            $seedPath = $seedDir.DIRECTORY_SEPARATOR.$f['name'];
            $publicPath = $publicDir.DIRECTORY_SEPARATOR.$f['name'];

            if (!$force && File::exists($seedPath)) {
                $this->line("Skip (exists): {$f['name']}  (use --force to overwrite)");
                if (!File::exists($publicPath)) {
                    File::copy($seedPath, $publicPath);
                }
                continue;
            }

            $phpWord = new PhpWord();
            $section = $phpWord->addSection();

            $section->addTitle($f['title'], 1);
            $section->addText('DOKUMEN CONTOH/DUMMY — untuk uji coba upload lampiran Web ULT FKIP. Tidak untuk dokumen resmi.');
            $section->addText('Tanggal dibuat: '.now()->toDateString().' (CONTOH)');
            $section->addTextBreak(1);

            foreach ($f['lines'] as $line) {
                $section->addText($line);
            }

            // Save as DOCX (Word2007). PhpWord output is macro-free.
            $writer = IOFactory::createWriter($phpWord, 'Word2007');
            $writer->save($seedPath);

            // DEV ONLY public copy for easy local upload testing
            File::copy($seedPath, $publicPath);

            $this->info("Generated: {$f['name']}");
        }

        // Mirror folder structure on default disk (local) to keep consistency with code/docs
        $disk = Storage::disk(config('filesystems.default', 'local'));
        if (!$disk->exists('seed/dummy-docx')) {
            $disk->makeDirectory('seed/dummy-docx');
        }

        $this->info('Done. Files available:');
        $this->line('- storage/app/seed/dummy-docx/ (private seed files)');
        $this->line('- public/demo-files/ (DEV ONLY; disable/remove for production)');

        return self::SUCCESS;
    }
}
