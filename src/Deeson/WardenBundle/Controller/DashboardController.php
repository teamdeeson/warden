<?php

namespace Deeson\WardenBundle\Controller;

use Deeson\WardenBundle\Managers\DashboardManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends Controller {

  public function indexAction() {
    /** @var DashboardManager $dashboardManager */
    $dashboardManager = $this->get('warden.dashboard_manager');
    $sites = $dashboardManager->getDocumentsBy(array(), array('name' => 'asc'));

    $params = array(
      'sites' => $sites,
    );

    return $this->render('DeesonWardenBundle:Dashboard:index.html.twig', $params);
  }

  public function unauthorisedAction() {
    $params = array(
      'status_text' => 'Unauthorised Access',
      'status_code' => '403',
    );

    return $this->render('DeesonWardenBundle:Dashboard:unauthorised.html.twig', $params);
  }

}
