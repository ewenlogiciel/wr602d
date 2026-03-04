<?php

namespace App\Controller;

use App\Entity\Tool;
use App\Repository\ToolRepository;
use App\Security\ToolVoter;
use App\Service\PdfGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PdfController extends AbstractController
{
    #[Route('/tools', name: 'app_tools')]
    #[IsGranted('ROLE_USER')]
    public function tools(ToolRepository $toolRepository): Response
    {
        $tools = $toolRepository->findBy(['is_active' => true], ['id' => 'ASC']);

        return $this->render('pdf/tools.html.twig', [
            'tools' => $tools,
        ]);
    }

    #[Route('/tools/{id}', name: 'app_tool_convert')]
    #[IsGranted('ROLE_USER')]
    public function convert(Tool $tool, Request $request, PdfGeneratorService $pdfGenerator): Response
    {
        if (!$this->isGranted(ToolVoter::ACCESS, $tool)) {
            $this->addFlash('error', 'Votre plan actuel ne donne pas accès à cet outil.');
            return $this->redirectToRoute('app_tools');
        }

        $pdfContent = null;
        $error = null;

        if ($request->isMethod('POST')) {
            try {
                $isUrlTool = str_contains($tool->getName(), 'URL') || str_contains($tool->getName(), 'Capture');

                if ($isUrlTool) {
                    $url = $request->request->get('url');
                    if ($url) {
                        $pdfContent = $pdfGenerator->generatePdfFromUrl($url);
                    }
                } else {
                    $file = $request->files->get('file');
                    if ($file) {
                        $htmlContent = file_get_contents($file->getPathname());
                        $pdfContent = $pdfGenerator->generatePdfFromHtml($htmlContent);
                    }
                }

                if ($pdfContent) {
                    return new Response($pdfContent, 200, [
                        'Content-Type'        => 'application/pdf',
                        'Content-Disposition' => 'inline; filename="generation.pdf"',
                    ]);
                }
            } catch (\Throwable $e) {
                $error = 'Une erreur est survenue lors de la conversion. Vérifiez votre source et réessayez.';
            }
        }

        return $this->render('pdf/convert.html.twig', [
            'tool'  => $tool,
            'error' => $error,
        ]);
    }
}
