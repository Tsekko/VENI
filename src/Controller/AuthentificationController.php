<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AuthentificationController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }
        $remember = null;
        $lastUsername = '';

        // Si l'on a un cookie user, on récupère sa valeur pour l'afficher dans le champ pseudo
        if ($request->cookies->get('user')) {
            dump($request->cookies->get('user'));
            $lastUsername = $request->cookies->get('user');
            $remember = true;
        }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        if ($authenticationUtils->getLastUsername() != "") {
            $lastUsername = $authenticationUtils->getLastUsername();
        }

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'remember_me' => $remember, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): Response
    {
        return $this->render('main/home.html.twig');
        //throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
