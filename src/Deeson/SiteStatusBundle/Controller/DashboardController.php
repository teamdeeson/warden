<?php

namespace Deeson\SiteStatusBundle\Controller;

use Deeson\SiteStatusBundle\Managers\SiteHaveIssueManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DashboardController extends Controller {

  public function indexAction() {
    /** @var SiteHaveIssueManager $siteHaveIssueManager */
    $siteHaveIssueManager = $this->get('site_have_issue_manager');
    $sites = $siteHaveIssueManager->getAllDocuments();

    $params = array(
      'sites' => $sites,
    );

    return $this->render('DeesonSiteStatusBundle:Dashboard:index.html.twig', $params);
  }

}
