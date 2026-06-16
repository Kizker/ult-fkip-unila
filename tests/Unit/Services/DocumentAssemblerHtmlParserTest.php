<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\Documents\DocumentAssemblerService;
use ReflectionMethod;

class DocumentAssemblerHtmlParserTest extends TestCase
{
    private function getPrivateMethod(string $methodName): ReflectionMethod
    {
        $ref = new \ReflectionClass(DocumentAssemblerService::class);
        $method = $ref->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }

    public function test_contains_html_detection(): void
    {
        $assembler = $this->app->make(DocumentAssemblerService::class);
        $containsHtml = $this->getPrivateMethod('containsHtml');

        $this->assertTrue($containsHtml->invoke($assembler, 'This has <b>bold</b> text'));
        $this->assertTrue($containsHtml->invoke($assembler, '<p>Paragraph</p>'));
        $this->assertTrue($containsHtml->invoke($assembler, 'Line break<br>here'));
        $this->assertTrue($containsHtml->invoke($assembler, 'List <ul><li>Item</li></ul>'));
        
        $this->assertFalse($containsHtml->invoke($assembler, 'This is plain text'));
        $this->assertFalse($containsHtml->invoke($assembler, 'This has brackets {{key}} but no HTML'));
    }

    public function test_html_parsing_and_openxml_structure(): void
    {
        $xml = '<w:p xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:r><w:t>Placeholder</w:t></w:r></w:p>';
        
        $dom = new \DOMDocument();
        $dom->loadXML($xml);
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        
        $tNode = $xpath->query('//w:t')->item(0);
        $this->assertNotNull($tNode);

        $assembler = $this->app->make(DocumentAssemblerService::class);
        $writeHtmlToWordRun = $this->getPrivateMethod('writeHtmlToWordRun');

        // Test simple styling tag: <b> and <i> and <u>
        $htmlInput = 'Normal <b>Bold</b> and <i>Italic</i> and <u>Underline</u>';
        $writeHtmlToWordRun->invoke($assembler, $tNode, $htmlInput);

        // Verify resulting XML
        $newRuns = $xpath->query('//w:r');
        // Original run is kept but its w:t is emptied. Cloned runs are inserted.
        $this->assertGreaterThan(1, $newRuns->length);

        $boldFound = false;
        $italicFound = false;
        $underlineFound = false;

        foreach ($newRuns as $run) {
            $rPr = $xpath->query('.//w:rPr', $run)->item(0);
            if ($rPr) {
                if ($xpath->query('.//w:b', $rPr)->length > 0) {
                    $boldFound = true;
                }
                if ($xpath->query('.//w:i', $rPr)->length > 0) {
                    $italicFound = true;
                }
                if ($xpath->query('.//w:u', $rPr)->length > 0) {
                    $underlineFound = true;
                }
            }
        }

        $this->assertTrue($boldFound, 'Failed to parse <b> tag to w:b');
        $this->assertTrue($italicFound, 'Failed to parse <i> tag to w:i');
        $this->assertTrue($underlineFound, 'Failed to parse <u> tag to w:u');
    }
}
