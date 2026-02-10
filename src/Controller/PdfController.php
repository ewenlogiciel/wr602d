<?php

namespace App\Controller;

use App\Service\PdfGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PdfController extends AbstractController
{
    #[Route('/pdf/generate', name: 'app_pdf_generate')]
    public function generate(PdfGeneratorService $pdfGenerator): Response
    {
        // Votre contenu HTML
        $htmlContent = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Mon PDF</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        padding: 20px;
                    }
                    h1 {
                        color: #333;
                    }
                </style>
            </head>
            <body>
                <h1>Bonjour depuis Gotenberg!</h1>
                <p>Ceci est un PDF généré dynamiquement.</p>
                <p>Date de génération: ' . date('d/m/Y H:i:s') . '</p>
            </body>
            </html>
        ';

        // Générez le PDF
        $pdfContent = $pdfGenerator->generatePdfFromHtml($htmlContent);

        // Retournez le PDF comme réponse
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="document.pdf"'
        ]);
    }
}
