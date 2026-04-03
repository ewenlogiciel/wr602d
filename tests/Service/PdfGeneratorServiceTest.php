<?php

namespace App\Tests\Service;

use App\Service\PdfGeneratorService;
use PHPUnit\Framework\TestCase;

class PdfGeneratorServiceTest extends TestCase
{
    public function testThrowsExceptionWhenGotenbergUnreachable(): void
    {
        $service = new PdfGeneratorService('http://invalid-gotenberg-host:9999');

        $this->expectException(\RuntimeException::class);
        $service->generatePdfFromUrl('https://example.com');
    }

    public function testThrowsExceptionForHtmlWhenUnreachable(): void
    {
        $service = new PdfGeneratorService('http://invalid-gotenberg-host:9999');

        $this->expectException(\RuntimeException::class);
        $service->generatePdfFromHtml('<html><body>Test</body></html>');
    }

    public function testThrowsExceptionForOfficeWhenUnreachable(): void
    {
        $service = new PdfGeneratorService('http://invalid-gotenberg-host:9999');

        $tmpFile = tempnam(sys_get_temp_dir(), 'test_') . '.docx';
        file_put_contents($tmpFile, 'fake docx content');

        try {
            $this->expectException(\RuntimeException::class);
            $service->generatePdfFromOffice($tmpFile, 'test.docx');
        } finally {
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }
    }

    public function testThrowsExceptionForMergeWhenUnreachable(): void
    {
        $service = new PdfGeneratorService('http://invalid-gotenberg-host:9999');

        $tmp1 = tempnam(sys_get_temp_dir(), 'pdf1_');
        $tmp2 = tempnam(sys_get_temp_dir(), 'pdf2_');
        file_put_contents($tmp1, '%PDF-1.4 fake');
        file_put_contents($tmp2, '%PDF-1.4 fake');

        try {
            $this->expectException(\RuntimeException::class);
            $service->mergePdfs([
                ['path' => $tmp1, 'name' => 'a.pdf'],
                ['path' => $tmp2, 'name' => 'b.pdf'],
            ]);
        } finally {
            unlink($tmp1);
            unlink($tmp2);
        }
    }

    public function testThrowsExceptionForPdfAWhenUnreachable(): void
    {
        $service = new PdfGeneratorService('http://invalid-gotenberg-host:9999');

        $tmpFile = tempnam(sys_get_temp_dir(), 'pdf_');
        file_put_contents($tmpFile, '%PDF-1.4 fake');

        try {
            $this->expectException(\RuntimeException::class);
            $service->convertToPdfA($tmpFile, 'test.pdf');
        } finally {
            unlink($tmpFile);
        }
    }
}
