<?php

namespace App\Controller;

use App\Entity\Generation;
use App\Entity\Tool;
use App\Repository\GenerationRepository;
use App\Repository\ToolRepository;
use App\Security\ToolVoter;
use App\Service\PdfGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use League\CommonMark\CommonMarkConverter;
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
    public function convert(Tool $tool, Request $request, PdfGeneratorService $pdfGenerator, EntityManagerInterface $em, GenerationRepository $generationRepository): Response
    {
        if (!$this->isGranted(ToolVoter::ACCESS, $tool)) {
            $this->addFlash('error', 'Votre plan actuel ne donne pas accès à cet outil.');
            return $this->redirectToRoute('app_tools');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $user  = $this->getUser();
            $limit = $user->getPlan()->getUsageLimit();

            if ($limit !== null) {
                $today      = new \DateTimeImmutable('today');
                $tomorrow   = $today->modify('+1 day');
                $usedToday  = $generationRepository->countByUserOnDate($user, $today, $tomorrow);

                if ($usedToday >= $limit) {
                    $error = sprintf('Vous avez atteint la limite de %d génération(s) par jour incluse dans votre abonnement.', $limit);
                    return $this->render('pdf/convert.html.twig', ['tool' => $tool, 'error' => $error]);
                }
            }
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
                        ];

                        if ($extension === 'pdf') {
                            $pdfContent = $pdfGenerator->convertToPdfA(
                                $uploadedFile->getPathname(),
                                $uploadedFile->getClientOriginalName()
                            );
                        } elseif ($extension === 'md' || $extension === 'markdown') {
                            $converter = new CommonMarkConverter();
                            $html = $converter->convert(file_get_contents($uploadedFile->getPathname()))->getContent();
                            $pdfContent = $pdfGenerator->generatePdfFromHtml(
                                '<!DOCTYPE html><html><head><meta charset="utf-8"><style>body{font-family:sans-serif;max-width:800px;margin:40px auto;padding:0 20px;line-height:1.6}pre{background:#f4f4f4;padding:1em;overflow-x:auto}code{background:#f4f4f4;padding:.2em .4em;border-radius:3px}</style></head><body>' . $html . '</body></html>'
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
