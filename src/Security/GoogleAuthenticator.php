<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Client\Provider\Google;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\{RedirectResponse, Response};
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class GoogleAuthenticator extends AbstractAuthenticator
{
    private Google $provider;
    private EntityManagerInterface $em;

    /**
     * @param Google $provider
     * @param EntityManagerInterface $em
     */
    public function __construct(Google $provider, EntityManagerInterface $em)
    {
        $this->provider = $provider;
        $this->em = $em;
    }

    public function supports(Request $request): ?bool
    {
        return $request->get('_route') == 'auth_google';
    }

    public function authenticate(Request $request): PassportInterface
    {
        $auth_code = $request->query->get('code');

        return new SelfValidatingPassport(
            new UserBadge($auth_code, [$this, 'getUserFromGoogleAuthCode']),
            [new RememberMeBadge]
        );
    }

    public function getUserFromGoogleAuthCode($auth_code)
    {
        // Exchange an authorization code for an access token
        $token = $this->provider->getAccessToken('authorization_code', ['code' => $auth_code]);
        $owner_details = $this->provider->getResourceOwner($token);

        $user_repo = $this->em->getRepository(User::class);
        $user = $user_repo->findOneBy(['email' => $owner_details->getEmail()]);

        if ($user) {
            return $user;
        }

        return $user;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewall_name): ?Response
    {
        return new RedirectResponse('/');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new RedirectResponse('/#/login?' . $exception->getMessage());
    }
}
