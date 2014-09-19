<?php

namespace Deeson\SiteStatusBundle\Controller;

use Deeson\SiteStatusBundle\Managers\NeedUpdateManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DashboardController extends Controller {

  public function indexAction() {
    /** @var NeedUpdateManager $needUpdateManager */
    $needUpdateManager = $this->get('need_update_manager');
    $sites = $needUpdateManager->getAllDocuments();

    $params = array(
      'sites' => $sites,
    );

    return $this->render('DeesonSiteStatusBundle:Dashboard:index.html.twig', $params);
  }

}
