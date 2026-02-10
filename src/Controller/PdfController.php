<?php

namespace App\Controller;

use App\Service\PdfGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PdfController extends AbstractController
{
    #[Route('/pdf/generate', name: 'app_pdf_generate')]
    #[IsGranted('ROLE_USER')]
    public function generate(Request $request, PdfGeneratorService $pdfGenerator): Response
    {
        $form = $this->createFormBuilder()
            ->add('url', UrlType::class, [
                'label' => 'URL du site à transformer en PDF',
                'attr' => ['placeholder' => 'https://example.com']
            ])
            ->add('submit', SubmitType::class, ['label' => 'Générer le PDF'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $url = $form->getData()['url'];

            $pdfContent = $pdfGenerator->generatePdfFromUrl($url);

            return new Response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="generation.pdf"'
            ]);
        }

        return $this->render('pdf/generate_pdf.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
