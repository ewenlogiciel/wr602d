<?php

namespace App\Service;

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
        // Créez un fichier HTML temporaire nommé index.html (important pour Gotenberg)
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
            // Nettoyez les fichiers temporaires
            unlink($tempHtmlFile);
            rmdir($tempDir);
        }
    }
}
