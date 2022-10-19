<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class AuthentificationAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function authenticate(Request $request): Passport
    {
        $pseudo = $request->request->get('pseudo', '');

        $request->getSession()->set(Security::LAST_USERNAME, $pseudo);

        return new Passport(
            new UserBadge($pseudo),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
        //     return new RedirectResponse($targetPath);
        // }
        // Attribution de la valeur de la checkbox à une variable
        $remember = $request->request->get('_remember_me');
        $cookie = null;
        $date = new \DateTime('+1 week');

        // On prépare en amont la réponse qui va rediriger vers la page d'accueil
        $response = new RedirectResponse(
            $this->urlGenerator->generate('app_home')
        );

        // Si la checkbox a été cochée, on crée un cookie qui va stocker le pseudo entré et on l'ajoute à la réponse
        if ($remember != null) {
            $cookie = Cookie::create('user')
                ->withValue($request->request->get('pseudo'))
                ->withExpires($date)
                ->withDomain('localhost')
                ->withSecure(true);
            $response->headers->setCookie($cookie);
        } else {
            // Dans le cas contraire et s'il existe un cookie user, on le supprime
            if ($request->cookies->get('user') != null) {
                $response->headers->clearCookie('user');
            }
        }

        // Envoi de la réponse
        return $response;
        //throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
