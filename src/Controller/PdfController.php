<?php

namespace App\Controller;

use App\Entity\Generation;
use App\Entity\Tool;
use App\Repository\ToolRepository;
use App\Security\ToolVoter;
use App\Service\PdfGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
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
    public function convert(Tool $tool, Request $request, PdfGeneratorService $pdfGenerator, EntityManagerInterface $em): Response
    {
        if (!$this->isGranted(ToolVoter::ACCESS, $tool)) {
            $this->addFlash('error', 'Votre plan actuel ne donne pas accès à cet outil.');
            return $this->redirectToRoute('app_tools');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            try {
                $isUrlTool   = str_contains($tool->getName(), 'URL') || str_contains($tool->getName(), 'Capture');
                $isMergeTool = str_contains($tool->getName(), 'Fusionner');
                $pdfContent  = null;
                $source      = null;

                if ($isUrlTool) {
                    $source = $request->request->get('url');
                    if ($source) {
                        $pdfContent = $pdfGenerator->generatePdfFromUrl($source);
                    }
                } elseif ($isMergeTool) {
                    $uploadedFiles = $request->files->get('files', []);
                    if (is_array($uploadedFiles) && count($uploadedFiles) >= 2) {
                        $source = count($uploadedFiles) . ' fichiers PDF';
                        $filesData = array_map(
                            fn($f) => ['path' => $f->getPathname(), 'name' => $f->getClientOriginalName()],
                            $uploadedFiles
                        );
                        $pdfContent = $pdfGenerator->mergePdfs($filesData);
                    }
                } else {
                    $uploadedFile = $request->files->get('file');
                    if ($uploadedFile) {
                        $source    = $uploadedFile->getClientOriginalName();
                        $extension = strtolower($uploadedFile->getClientOriginalExtension());

                        $libreOfficeExtensions = [
                            'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
                            'odt', 'ods', 'odp', 'rtf', 'csv', 'txt',
                            'png', 'jpg', 'jpeg', 'webp', 'svg',
                            'md', 'markdown',
                        ];

                        if ($extension === 'pdf') {
                            $pdfContent = $pdfGenerator->convertToPdfA(
                                $uploadedFile->getPathname(),
                                $uploadedFile->getClientOriginalName()
                            );
                        } elseif (in_array($extension, $libreOfficeExtensions, true)) {
                            $pdfContent = $pdfGenerator->generatePdfFromOffice(
                                $uploadedFile->getPathname(),
                                $uploadedFile->getClientOriginalName()
                            );
                        } else {
                            // HTML → Chromium
                            $pdfContent = $pdfGenerator->generatePdfFromHtml(
                                file_get_contents($uploadedFile->getPathname())
                            );
                        }
                    }
                }

                if ($pdfContent) {
                    $filename = $this->savePdf($pdfContent);

                    $generation = (new Generation())
                        ->setUser($this->getUser())
                        ->setTool($tool)
                        ->setSource($source)
                        ->setFile($filename)
                        ->setCreatedAt(new \DateTimeImmutable());

                    $em->persist($generation);
                    $em->flush();

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

    private function savePdf(string $content): string
    {
        $dir = $this->getParameter('kernel.project_dir') . '/var/pdf';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = uniqid('gen_', true) . '.pdf';
        file_put_contents($dir . '/' . $filename, $content);

        return $filename;
    }
}
