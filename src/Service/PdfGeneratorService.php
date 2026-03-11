<?php

namespace App\Service;

class PdfGeneratorService
{
    public function __construct(private string $gotenbergUrl) {}

    public function generatePdfFromUrl(string $url): string
    {
        $boundary = $this->boundary();
        $body     = $this->field($boundary, 'url', $url) . "--{$boundary}--\r\n";

        return $this->post('/forms/chromium/convert/url', $boundary, $body);
    }

    public function generatePdfFromHtml(string $htmlContent): string
    {
        $boundary = $this->boundary();
        $body     = $this->file($boundary, 'files', 'index.html', $htmlContent) . "--{$boundary}--\r\n";

        return $this->post('/forms/chromium/convert/html', $boundary, $body);
    }

    public function generatePdfFromOffice(string $filePath, string $filename): string
    {
        $boundary = $this->boundary();
        $body     = $this->file($boundary, 'files', $filename, file_get_contents($filePath)) . "--{$boundary}--\r\n";

        return $this->post('/forms/libreoffice/convert', $boundary, $body);
    }

    /**
     * @param array<array{path: string, name: string}> $files
     */
    public function mergePdfs(array $files): string
    {
        $boundary = $this->boundary();
        $body     = '';
        foreach ($files as $f) {
            $body .= $this->file($boundary, 'files', $f['name'], file_get_contents($f['path']));
        }
        $body .= "--{$boundary}--\r\n";

        return $this->post('/forms/pdfengines/merge', $boundary, $body);
    }

    public function convertToPdfA(string $filePath, string $filename): string
    {
        $boundary = $this->boundary();
        $body     = $this->field($boundary, 'pdfFormat', 'PDF/A-2b')
                  . $this->file($boundary, 'files', $filename, file_get_contents($filePath))
                  . "--{$boundary}--\r\n";

        return $this->post('/forms/pdfengines/convert', $boundary, $body);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function post(string $route, string $boundary, string $body): string
    {
        $ctx = stream_context_create(['http' => [
            'method'        => 'POST',
            'header'        => "Content-Type: multipart/form-data; boundary={$boundary}",
            'content'       => $body,
            'timeout'       => 60,
            'ignore_errors' => true,
        ]]);

        $result = file_get_contents($this->gotenbergUrl . $route, false, $ctx);

        if ($result === false) {
            throw new \RuntimeException("Impossible de joindre Gotenberg ({$this->gotenbergUrl}{$route})");
        }

        // Récupère le statut HTTP
        $status = 0;
        foreach ($http_response_header ?? [] as $header) {
            if (preg_match('#HTTP/\S+ (\d+)#', $header, $m)) {
                $status = (int) $m[1];
            }
        }

        if ($status < 200 || $status >= 300) {
            throw new \RuntimeException("Gotenberg a retourné HTTP {$status} : " . substr($result, 0, 300));
        }

        return $result;
    }

    private function field(string $boundary, string $name, string $value): string
    {
        return "--{$boundary}\r\n"
            . "Content-Disposition: form-data; name=\"{$name}\"\r\n"
            . "\r\n"
            . $value . "\r\n";
    }

    private function file(string $boundary, string $name, string $filename, string $content): string
    {
        return "--{$boundary}\r\n"
            . "Content-Disposition: form-data; name=\"{$name}\"; filename=\"{$filename}\"\r\n"
            . "Content-Type: application/octet-stream\r\n"
            . "\r\n"
            . $content . "\r\n";
    }

    private function boundary(): string
    {
        return '----GotenbergBoundary' . bin2hex(random_bytes(8));
    }
}
