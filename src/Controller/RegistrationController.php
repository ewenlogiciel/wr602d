<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private VerifyEmailHelperInterface $verifyEmailHelper) {}

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword($user, $form->get('plainPassword')->getData())
            );
            $user->setIsVerified(false);

            $entityManager->persist($user);
            $entityManager->flush();

            // Génération du lien de vérification signé
            $signatureComponents = $this->verifyEmailHelper->generateSignature(
                'app_verify_email',
                (string) $user->getId(),
                $user->getEmail(),
                ['id' => $user->getId()]
            );

            // Envoi du mail de confirmation
            $email = (new TemplatedEmail())
                ->from(new Address('no-reply@polypdf.fr', 'PDFactory'))
                ->to($user->getEmail())
                ->subject('Confirmez votre adresse e-mail — PDFactory')
                ->htmlTemplate('emails/confirmation_email.html.twig')
                ->context([
                    'user'       => $user,
                    'signedUrl'  => $signatureComponents->getSignedUrl(),
                    'expiresAt'  => $signatureComponents->getExpiresAt(),
                ]);

            $mailer->send($email);

            // Stocke l'email en session pour l'afficher sur la page de confirmation
            $request->getSession()->set('registration_email', $user->getEmail());

            return $this->redirectToRoute('app_check_email');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/check-email', name: 'app_check_email')]
    public function checkEmail(Request $request): Response
    {
        $email = $request->getSession()->get('registration_email');

        // Empêche l'accès direct sans inscription préalable
        if (!$email) {
            return $this->redirectToRoute('app_register');
        }

        $request->getSession()->remove('registration_email');

        return $this->render('registration/check_email.html.twig', [
            'email' => $email,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $id   = $request->query->get('id');
        $user = $id ? $userRepository->find($id) : null;

        if (!$user) {
            $this->addFlash('error', 'Lien de vérification invalide.');
            return $this->redirectToRoute('app_register');
        }

        try {
            $this->verifyEmailHelper->validateEmailConfirmation(
                $request->getUri(),
                (string) $user->getId(),
                $user->getEmail()
            );
        } catch (VerifyEmailExceptionInterface $e) {
            $this->addFlash('error', $e->getReason());
            return $this->redirectToRoute('app_register');
        }

        $user->setIsVerified(true);
        $entityManager->flush();

        $plan = $user->getPlan();
        if ($plan !== null && $plan->getStripePriceId() !== null) {
            $checkoutUrl = $this->generateUrl(
                'app_payment_checkout',
                ['id' => $plan->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $request->getSession()->set('_security.main.target_path', $checkoutUrl);
            $this->addFlash('success', 'Votre adresse e-mail a été vérifiée. Connectez-vous pour finaliser votre abonnement.');
        } else {
            $this->addFlash('success', 'Votre adresse e-mail a été vérifiée. Vous pouvez maintenant vous connecter !');
        }

        return $this->redirectToRoute('app_login');
    }
}
