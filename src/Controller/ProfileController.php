<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profile')]
#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('', name: 'app_profile', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('profile_success', 'Profil mis à jour avec succès.');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/index.html.twig', [
            'profileForm' => $form,
        ]);
    }

    #[Route('/password', name: 'app_profile_password', methods: ['POST'])]
    public function changePassword(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if (!$this->isCsrfTokenValid('profile_password', $request->request->get('_token'))) {
            $this->addFlash('password_error', 'Token invalide.');
            return $this->redirectToRoute('app_profile');
        }

        $currentPassword = $request->request->get('current_password');
        $newPassword     = $request->request->get('new_password');
        $confirmPassword = $request->request->get('confirm_password');

        if (!$hasher->isPasswordValid($user, $currentPassword)) {
            $this->addFlash('password_error', 'Mot de passe actuel incorrect.');
            return $this->redirectToRoute('app_profile');
        }

        if (strlen($newPassword) < 6) {
            $this->addFlash('password_error', 'Le nouveau mot de passe doit contenir au moins 6 caractères.');
            return $this->redirectToRoute('app_profile');
        }

        if ($newPassword !== $confirmPassword) {
            $this->addFlash('password_error', 'Les nouveaux mots de passe ne correspondent pas.');
            return $this->redirectToRoute('app_profile');
        }

        $user->setPassword($hasher->hashPassword($user, $newPassword));
        $em->flush();

        $this->addFlash('password_success', 'Mot de passe modifié avec succès.');
        return $this->redirectToRoute('app_profile');
    }
}
