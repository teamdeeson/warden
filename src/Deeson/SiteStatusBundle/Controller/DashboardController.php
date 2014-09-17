<?php

namespace Deeson\SiteStatusBundle\Controller;

use Deeson\SiteStatusBundle\Managers\SiteManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DashboardController extends Controller {

  public function indexAction() {
    /** @var SiteManager $manager */
    $manager = $this->get('site_manager');
    $sites = $manager->getAllSitesWithErrors();

    $params = array(
      'sites' => $sites,
    );

    return $this->render('DeesonSiteStatusBundle:Dashboard:index.html.twig', $params);
  }

}
