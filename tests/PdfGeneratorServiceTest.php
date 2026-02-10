<?php

namespace App\Tests;

use App\Service\PdfGeneratorService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class PdfGeneratorServiceTest extends TestCase
{
    public function testGeneratePdfFromHtmlSuccess(): void
    {
        // Arrange : Préparez les données de test
        $htmlContent = '<html><body><h1>Test PDF</h1></body></html>';
        $expectedPdfContent = '%PDF-1.4 fake pdf content';

        // Mock de la réponse HTTP
        $mockResponse = new MockResponse($expectedPdfContent, [
            'http_code' => 200,
            'response_headers' => ['Content-Type' => 'application/pdf'],
        ]);

        $httpClient = new MockHttpClient($mockResponse);

        // Créez le service avec le client mocké
        $pdfGenerator = new PdfGeneratorService($httpClient, 'http://gotenberg:3000');

        // Act : Appelez la méthode à tester
        $result = $pdfGenerator->generatePdfFromHtml($htmlContent);

        // Assert : Vérifiez les résultats
        $this->assertNotEmpty($result);
        $this->assertEquals($expectedPdfContent, $result);
        $this->assertStringContainsString('%PDF', $result);
    }

    public function testGeneratePdfFromHtmlWithEmptyContent(): void
    {
        // Arrange
        $htmlContent = '';
        $expectedPdfContent = '%PDF-1.4';

        $mockResponse = new MockResponse($expectedPdfContent, [
            'http_code' => 200,
        ]);

        $httpClient = new MockHttpClient($mockResponse);
        $pdfGenerator = new PdfGeneratorService($httpClient, 'http://gotenberg:3000');

        // Act
        $result = $pdfGenerator->generatePdfFromHtml($htmlContent);

        // Assert
        $this->assertNotEmpty($result);
    }

    public function testGeneratePdfFromHtmlWithComplexHtml(): void
    {
        // Arrange
        $htmlContent = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Document complexe</title>
                <style>
                    body { font-family: Arial; }
                    h1 { color: red; }
                </style>
            </head>
            <body>
                <h1>Titre</h1>
                <p>Paragraphe avec <strong>texte en gras</strong></p>
                <ul>
                    <li>Item 1</li>
                    <li>Item 2</li>
                </ul>
            </body>
            </html>
        ';

        $mockResponse = new MockResponse('%PDF-1.4 complex content', [
            'http_code' => 200,
        ]);

        $httpClient = new MockHttpClient($mockResponse);
        $pdfGenerator = new PdfGeneratorService($httpClient, 'http://gotenberg:3000');

        // Act
        $result = $pdfGenerator->generatePdfFromHtml($htmlContent);

        // Assert
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('%PDF', $result);
    }

    public function testGotenbergUrlIsUsedCorrectly(): void
    {
        // Arrange
        $customUrl = 'http://custom-gotenberg:9000';
        $htmlContent = '<html><body>Test</body></html>';

        $mockResponse = new MockResponse('%PDF-1.4', [
            'http_code' => 200,
        ]);

        // Vérifiez que l'URL correcte est utilisée
        $httpClient = new MockHttpClient(function ($method, $url) use ($mockResponse, $customUrl) {
            $this->assertEquals('POST', $method);
            $this->assertStringStartsWith($customUrl, $url);
            $this->assertStringContainsString('/forms/chromium/convert/html', $url);

            return $mockResponse;
        });

        $pdfGenerator = new PdfGeneratorService($httpClient, $customUrl);

        // Act
        $result = $pdfGenerator->generatePdfFromHtml($htmlContent);

        // Assert
        $this->assertNotEmpty($result);
    }
}
