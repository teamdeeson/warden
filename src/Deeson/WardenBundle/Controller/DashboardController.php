<?php

namespace Deeson\WardenBundle\Controller;

use Deeson\WardenBundle\Event\DashboardListEvent;
use Deeson\WardenBundle\Event\WardenEvents;
use Deeson\WardenBundle\Managers\DashboardManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Deeson\WardenBundle\Document\DashboardDocument;

class DashboardController extends Controller {

  public function indexAction() {
    /** @var DashboardManager $dashboardManager */
    $dashboardManager = $this->get('warden.dashboard_manager');
    $sites = $dashboardManager->getDocumentsBy(array(), array('name' => 'asc'));

    /** @var EventDispatcher $dispatcher */
    $dispatcher = $this->get('event_dispatcher');

    $siteList = array();
    foreach ($sites as $site) {
      /** @var DashboardDocument $site */
      $event = new DashboardListEvent($site);
      $dispatcher->dispatch(WardenEvents::WARDEN_DASHBOARD_LIST, $event);

      $siteList[] = array(
        'id' => $site->getSiteId(),
        'name' => $site->getName(),
        'url' => $site->getUrl(),
        'iconPath' => $event->getSiteTypeLogoPath(),
        'issuesCount' => $event->getSiteIssuesCount(),
        'critical' => $site->getHasCriticalIssue(),
      );
    }

    $params = array(
      'sites' => $siteList,
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
