<?php

namespace Deeson\WardenBundle\Controller;

use Deeson\WardenBundle\Security\WebserviceUserProvider;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends Controller {

    public function loginAction(Request $request) {
      /** @var WebserviceUserProvider $userProvider */
      $userProvider = $this->container->get('warden.user_provider');
      if (!$userProvider->isSetup()) {
        return $this->render('DeesonWardenBundle:Security:install.html.twig');
      }

      $authenticationUtils = $this->get('security.authentication_utils');
      return $this->render('DeesonWardenBundle:Security:login.html.twig',
        array(
          'last_username' => $authenticationUtils->getLastUsername(),
          'error' => $authenticationUtils->getLastAuthenticationError(),
        )
      );
    }

    public function loginCheckAction() {
    }

    public function logoutAction() {
    }
}
