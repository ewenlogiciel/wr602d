<?php

namespace App\Service;

use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PdfGeneratorService
{
    private HttpClientInterface $httpClient;
    private string $gotenbergUrl;

    public function __construct(HttpClientInterface $httpClient, string $gotenbergUrl)
    {
        $this->httpClient = $httpClient;
        $this->gotenbergUrl = $gotenbergUrl;
    }

    public function generatePdfFromHtml(string $htmlContent): string
    {
        $tempDir = sys_get_temp_dir() . '/gotenberg_' . uniqid();
        mkdir($tempDir);
        $tempHtmlFile = $tempDir . '/index.html';
        file_put_contents($tempHtmlFile, $htmlContent);

        try {
            $response = $this->httpClient->request('POST', $this->gotenbergUrl . '/forms/chromium/convert/html', [
                'body' => [
                    'files' => fopen($tempHtmlFile, 'r'),
                ]
            ]);

            return $response->getContent();
        } finally {
            unlink($tempHtmlFile);
            rmdir($tempDir);
        }
    }

    public function generatePdfFromOffice(string $filePath, string $filename): string
    {
        $formData = new FormDataPart([
            'files' => DataPart::fromPath($filePath, $filename),
        ]);

        $response = $this->httpClient->request('POST', $this->gotenbergUrl . '/forms/libreoffice/convert', [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body'    => $formData->bodyToIterable(),
        ]);

        return $response->getContent();
    }

    public function generatePdfFromUrl(string $url): string
    {
        $formData = new FormDataPart(['url' => $url]);

        $response = $this->httpClient->request('POST', $this->gotenbergUrl . '/forms/chromium/convert/url', [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body' => $formData->bodyToIterable(),
        ]);

        return $response->getContent();
    }

    /**
     * @param array<array{path: string, name: string}> $files
     */
    public function mergePdfs(array $files): string
    {
        $parts = [];
        foreach ($files as $i => $file) {
            $parts['file_' . $i] = DataPart::fromPath($file['path'], $file['name']);
        }

        $formData = new FormDataPart($parts);

        $response = $this->httpClient->request('POST', $this->gotenbergUrl . '/forms/pdfengines/merge', [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body'    => $formData->bodyToIterable(),
        ]);

        return $response->getContent();
    }

    public function convertToPdfA(string $filePath, string $filename): string
    {
        $formData = new FormDataPart([
            'files'     => DataPart::fromPath($filePath, $filename),
            'pdfFormat' => 'PDF/A-2b',
        ]);

        $response = $this->httpClient->request('POST', $this->gotenbergUrl . '/forms/pdfengines/convert', [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body'    => $formData->bodyToIterable(),
        ]);

        return $response->getContent();
    }
}
