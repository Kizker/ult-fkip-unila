<?php

namespace Tests\Unit\Services;

use App\Services\Documents\CertificateDocumentService;
use Tests\TestCase;

class CertificateDocumentServicePlaceholderTest extends TestCase
{
    public function test_process_slide_xml_replaces_split_placeholder_tokens_within_same_paragraph(): void
    {
        $service = app(CertificateDocumentService::class);

        $tmpRoot = storage_path('app/tmp/tests/certificate_placeholders_'.uniqid('', true));
        @mkdir($tmpRoot, 0775, true);

        $slidePath = $tmpRoot.'/slide1.xml';
        $relsPath = $tmpRoot.'/slide1.xml.rels';
        $mediaDir = $tmpRoot.'/media';
        @mkdir($mediaDir, 0775, true);

        $slideXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<p:sld xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <p:cSld>
    <p:spTree>
      <p:sp>
        <p:nvSpPr>
          <p:cNvPr id="1" name="Shape 1"/>
          <p:cNvSpPr/>
          <p:nvPr/>
        </p:nvSpPr>
        <p:spPr>
          <a:xfrm>
            <a:off x="0" y="0"/>
            <a:ext cx="1000" cy="1000"/>
          </a:xfrm>
          <a:prstGeom prst="rect"><a:avLst/></a:prstGeom>
        </p:spPr>
        <p:txBody>
          <a:bodyPr/>
          <a:lstStyle/>
          <a:p>
            <a:r><a:t>No: {{</a:t></a:r>
            <a:r><a:t>nomor_surat</a:t></a:r>
            <a:r><a:t>}}</a:t></a:r>
          </a:p>
          <a:p>
            <a:r><a:t>Bandar Lampung, {{</a:t></a:r>
            <a:r><a:t>tanggal_ttd</a:t></a:r>
            <a:r><a:t>}}</a:t></a:r>
          </a:p>
          <a:p>
            <a:r><a:t>{{nama_penerima}}</a:t></a:r>
          </a:p>
        </p:txBody>
      </p:sp>
    </p:spTree>
  </p:cSld>
</p:sld>
XML;

        $relsXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"/>
XML;

        file_put_contents($slidePath, $slideXml);
        file_put_contents($relsPath, $relsXml);

        $method = new \ReflectionMethod($service, 'processSlideXml');
        $method->setAccessible(true);

        $mediaCache = [];
        $args = [
            $slidePath,
            $relsPath,
            [
                'nomor_surat' => '00001/UN26.14/PN.01.00/2026',
                'tanggal_ttd' => '20 Februari 2026',
                'nama_penerima' => 'Mahasiswa Demo',
            ],
            [],
            (string) config('ult.private_disk'),
            $mediaDir,
            &$mediaCache,
        ];
        $method->invokeArgs($service, $args);

        $rendered = file_get_contents($slidePath);
        $this->assertIsString($rendered);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $this->assertTrue(@$dom->loadXML((string) $rendered));

        $xp = new \DOMXPath($dom);
        $xp->registerNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');

        $paragraphs = $xp->query('//a:p');
        $this->assertNotFalse($paragraphs);
        $this->assertGreaterThanOrEqual(3, $paragraphs?->length ?? 0);

        $line1 = '';
        $line2 = '';
        $line3 = '';

        if ($paragraphs !== false) {
            $line1 = $this->joinParagraphText($xp, $paragraphs->item(0));
            $line2 = $this->joinParagraphText($xp, $paragraphs->item(1));
            $line3 = $this->joinParagraphText($xp, $paragraphs->item(2));
        }

        $this->assertSame('No: 00001/UN26.14/PN.01.00/2026', $line1);
        $this->assertSame('Bandar Lampung, 20 Februari 2026', $line2);
        $this->assertSame('Mahasiswa Demo', $line3);
        $this->assertStringNotContainsString('{{nomor_surat}}', (string) $rendered);
        $this->assertStringNotContainsString('{{tanggal_ttd}}', (string) $rendered);
        $this->assertStringNotContainsString('{{nama_penerima}}', (string) $rendered);

        @unlink($slidePath);
        @unlink($relsPath);
        @rmdir($mediaDir);
        @rmdir($tmpRoot);
    }

    public function test_extract_placeholder_tokens_reads_split_tokens_within_same_paragraph(): void
    {
        $service = app(CertificateDocumentService::class);

        $tmpRoot = storage_path('app/tmp/tests/certificate_placeholder_tokens_'.uniqid('', true));
        @mkdir($tmpRoot, 0775, true);

        $pptxPath = $tmpRoot.'/tokens.pptx';

        $slideXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<p:sld xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <p:cSld>
    <p:spTree>
      <p:sp>
        <p:txBody>
          <a:bodyPr/>
          <a:lstStyle/>
          <a:p>
            <a:r><a:t>{{</a:t></a:r>
            <a:r><a:t>nomor_surat</a:t></a:r>
            <a:r><a:t>}}</a:t></a:r>
          </a:p>
          <a:p>
            <a:r><a:t>Bandar Lampung, {{</a:t></a:r>
            <a:r><a:t>tanggal_ttd</a:t></a:r>
            <a:r><a:t>}}</a:t></a:r>
          </a:p>
          <a:p>
            <a:r><a:t>{{ttd_1}}</a:t></a:r>
          </a:p>
        </p:txBody>
      </p:sp>
    </p:spTree>
  </p:cSld>
</p:sld>
XML;

        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($pptxPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true);
        $zip->addFromString('ppt/slides/slide1.xml', $slideXml);
        $zip->close();

        $method = new \ReflectionMethod($service, 'extractPlaceholderTokensFromPptxFile');
        $method->setAccessible(true);

        /** @var array<int,string> $tokens */
        $tokens = $method->invoke($service, $pptxPath);

        $this->assertContains('nomor_surat', $tokens);
        $this->assertContains('tanggal_ttd', $tokens);
        $this->assertContains('ttd_1', $tokens);

        @unlink($pptxPath);
        @rmdir($tmpRoot);
    }

    private function joinParagraphText(\DOMXPath $xp, ?\DOMNode $paragraph): string
    {
        if (!($paragraph instanceof \DOMNode)) {
            return '';
        }

        $texts = $xp->query('.//a:t', $paragraph);
        if ($texts === false) {
            return '';
        }

        $out = '';
        foreach ($texts as $node) {
            $out .= (string) $node->textContent;
        }

        return $out;
    }
}
