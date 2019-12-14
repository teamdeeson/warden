<?php

namespace Deeson\WardenBundle\Controller;

use Deeson\WardenBundle\Document\UserDocument;
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
    /** @var UserDocument $user */
    $user = $this->getUser();
    //printf('<pre>%s</pre>', print_r([$user->getUsername(),$user->getGroupIds()], true));

    // @todo find sites that are in the group ids that the user has or that don't have a group id set
    $sites = $dashboardManager->getDocumentsBy([], ['name' => 'asc']);

    /** @var EventDispatcher $dispatcher */
    $dispatcher = $this->get('event_dispatcher');

    $siteList = [];
    foreach ($sites as $site) {
      /** @var DashboardDocument $site */
      $event = new DashboardListEvent($site);
      $dispatcher->dispatch(WardenEvents::WARDEN_DASHBOARD_LIST, $event);

      $siteList[] = [
        'id' => $site->getSiteId(),
        'name' => $site->getName(),
        'url' => $site->getUrl(),
        'iconPath' => $event->getSiteTypeLogoPath(),
        'issuesCount' => $event->getSiteIssuesCount(),
        'critical' => $site->getHasCriticalIssue(),
      ];
    }

    $params = [
      'sites' => $siteList,
    ];

    return $this->render('DeesonWardenBundle:Dashboard:index.html.twig', $params);
  }

  public function unauthorisedAction() {
    $params = [
      'status_text' => 'Unauthorised Access',
      'status_code' => '403',
    ];

    return $this->render('DeesonWardenBundle:Dashboard:unauthorised.html.twig', $params);
  }

}
