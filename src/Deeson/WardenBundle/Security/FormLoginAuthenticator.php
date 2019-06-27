<?php
namespace Deeson\WardenBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Core\Security;

class FormLoginAuthenticator extends AbstractFormLoginAuthenticator
{
    private $router;
    private $encoder;

    public function __construct(RouterInterface $router, UserPasswordEncoderInterface $encoder) {
        $this->router = $router;
        $this->encoder = $encoder;
    }

    public function supports(Request $request) {
        return $request->getPathInfo() == '/login' && $request->isMethod('POST');
    }

    public function getCredentials(Request $request) {
        $username = $request->request->get('_username');
        $request->getSession()->set(Security::LAST_USERNAME, $username);
        $password = $request->request->get('_password');

        return [
            'username' => $username,
            'password' => $password,
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider) {
        $username = $credentials['username'];
        return $userProvider->loadUserByUsername($username);
    }

    public function checkCredentials($credentials, UserInterface $user) {
        $plainPassword = $credentials['password'];
        if ($this->encoder->isPasswordValid($user, $plainPassword)) {
            return true;
        }

        throw new BadCredentialsException();
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey) {
        $url = $this->router->generate('_dashboard');

        return new RedirectResponse($url);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) {
       $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);

       $url = $this->router->generate('login');

       return new RedirectResponse($url);
    }

    protected function getLoginUrl() {
        return $this->router->generate('login');
    }

    protected function getDefaultSuccessRedirectUrl() {
        return $this->router->generate('_dashboard');
    }

    public function supportsRememberMe() {
        return false;
    }
}
