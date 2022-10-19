<?php

namespace App\Controller;

use App\Form\ProfilType;
use App\Security\AuthentificationAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class ParticipantController extends AbstractController
{
    #[Route('/participant', name: 'app_participant')]
    public function index(): Response
    {
        return $this->render('participant/index.html.twig', [
            'controller_name' => 'ParticipantController',
        ]);
    }

    #[Route('/monprofil', name: 'app_monprofil')]
    public function monProfil(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, AuthentificationAuthenticator $authenticator,UserAuthenticatorInterface $userAuthenticator ): Response
    {
        $user = $this ->getUser();
        $form = $this ->createForm(ProfilType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            if ($form->get('password')->getData()) {
                // encode the plain password
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
                $this->addFlash('succes', 'Le mot de passe a bien été changé');

            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('succes', 'Les modifications ont bien été faites');
            return $this->redirectToRoute('app_monprofil');
        }

        elseif ($form->isSubmitted() && !$form->isValid()) {


            $this->addFlash('error', 'Le formulaire n\'est pas valide : Le mail est déjà utilisé');
            return $this->redirectToRoute('app_home');

        }

        return $this->render('participant/monProfil.html.twig', [
            'monProfilForm' => $form->createView(),
        ]);

    }

}
