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
    public function monProfil(): Response
    {
        $user = $this ->getUser();
        $form = $this->createForm(ProfilType::class, $user);

        return $this->render('participant/monProfil.html.twig', [
            'monProfilForm' => $form->createView(),
        ]);
    }
}
