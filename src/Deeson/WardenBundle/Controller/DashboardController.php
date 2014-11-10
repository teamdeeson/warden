<?php

namespace Deeson\WardenBundle\Controller;

use Deeson\WardenBundle\Managers\SiteHaveIssueManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends Controller {

  public function indexAction() {
    /** @var SiteHaveIssueManager $siteHaveIssueManager */
    $siteHaveIssueManager = $this->get('site_have_issue_manager');
    $sites = $siteHaveIssueManager->getAllDocuments();

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
