<?php

namespace Tests\Support;

use ZipArchive;

class DocxBuilder
{
    /**
     * Create a minimal DOCX (zip) with the given plain text in word/document.xml.
     * Returns absolute file path.
     */
    public static function makeTempDocx(string $documentText): string
    {
        $dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'ult_docx_tests';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $path = $dir.DIRECTORY_SEPARATOR.uniqid('t_', true).'.docx';
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Cannot create docx.');
        }

        // Minimal files for zip validity; extraction/replacement only needs word/document.xml.
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
            .'</Types>');

        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
            .'</Relationships>');

        $zip->addFromString('word/_rels/document.xml.rels', '<?xml version="1.0" encoding="UTF-8"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"></Relationships>');

        $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8"?>'
            .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            .'<w:body>'
            .'<w:p><w:r><w:t>'.htmlspecialchars($documentText, ENT_XML1).'</w:t></w:r></w:p>'
            .'</w:body>'
            .'</w:document>');

        $zip->close();
        return $path;
    }
}

