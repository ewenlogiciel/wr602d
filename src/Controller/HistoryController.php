<?php

namespace App\Controller;

use App\Entity\Generation;
use App\Repository\GenerationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/history')]
#[IsGranted('ROLE_BASIC')]
class HistoryController extends AbstractController
{
    #[Route('', name: 'app_history')]
    public function index(GenerationRepository $repository): Response
    {
        $generations = $repository->findByUserOrderedByDate($this->getUser());

        return $this->render('history/index.html.twig', [
            'generations' => $generations,
        ]);
    }

    #[Route('/{id}/download', name: 'app_history_download')]
    public function download(Generation $generation): BinaryFileResponse
    {
        if ($generation->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $path = $this->getParameter('kernel.project_dir') . '/var/pdf/' . $generation->getFile();

        if (!file_exists($path)) {
            throw $this->createNotFoundException('Fichier introuvable.');
        }

        return new BinaryFileResponse($path, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="generation.pdf"',
        ]);
    }
}
