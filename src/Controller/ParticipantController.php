<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\User;
use App\Form\ProfilType;
use App\Form\RegistrationFormType;
use App\Security\AuthentificationAuthenticator;
use App\Security\UserAuthenticator;
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
    public function monProfil(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, AuthentificationAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        $user = $this ->getUser();
        $form = $this->createForm(ProfilType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

/*            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );*/

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('participant/monProfil.html.twig', [
            'monProfilForm' => $form->createView(),
        ]);
    }
}
