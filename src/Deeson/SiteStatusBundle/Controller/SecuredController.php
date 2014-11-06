<?php

namespace Deeson\SiteStatusBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/")
 */
class SecuredController extends Controller {

  /**
   * @Route("/login", name="_login")
   * @Template()
   */
  public function loginAction(Request $request) {
    $userFile = $this->container->getParameter('user_config_file');
    if (!file_exists($userFile)) {
      $params = array(
        'config_file' => $userFile
      );
      return $this->render('DeesonSiteStatusBundle:Secured:config_error.html.twig', $params);
    }

    if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
      $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
    }
    else {
      $error = $request->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
    }

    return array(
      'last_username' => $request->getSession()->get(SecurityContext::LAST_USERNAME),
      'error' => $error,
    );
  }

  /**
   * @Route("/login_check", name="_security_check")
   */
  public function securityCheckAction(Request $request) {
    // The security layer will intercept this request
  }

  /**
   * @Route("/logout", name="_logout")
   */
  public function logoutAction() {
    // The security layer will intercept this request
  }

}
