<?php

namespace Deeson\WardenBundle\Managers;

use Deeson\WardenBundle\Document\DashboardDocument;
use Deeson\WardenBundle\Document\SiteDocument;
use Deeson\WardenBundle\Event\DashboardUpdateEvent;

class DashboardManager extends BaseManager {

  /**
   * @return string
   *   The type of this manager.
   *   e.g. 'DashboardDocument'
   */
  public function getType() {
    return 'DashboardDocument';
  }

  /**
   * Create a new empty type of the object.
   *
   * @return DashboardDocument
   */
  public function makeNewItem() {
    return new DashboardDocument();
  }

  /**
   * Event: warden.dashboard.update
   *
   * Fires when the dashboard needs might need to be updated.
   *
   * A check is done on the site to see if it should appear on the dashboard.
   *
   * @param DashboardUpdateEvent $event
   */
  public function onWardenDashboardUpdate(DashboardUpdateEvent $event) {
    /** @var SiteDocument $site */
    $site = $event->getSite();

    $qb = $this->createQueryBuilder();
    $qb->field('siteId')->equals(new \MongoId($site->getId()));
    $cursor = $qb->getQuery()->execute()->toArray();
    $dashboardSite = array_pop($cursor);
    if (!empty($dashboardSite)) {
      $this->logger->addInfo('Remove the site [' . $site->getName() . '] from the dashboard');
      $this->deleteDocument($dashboardSite->getId());
    }

    if ($event->isForceDelete()) {
      return;
    }

    $this->addSiteToDashboard($site);
  }

  /**
   * Adds the site to the dashboard, if needed.
   *
   * @param \Deeson\WardenBundle\Document\SiteDocument $site
   *
   * @return bool
   *   True if the site has been added otherwise false.
   */
  public function addSiteToDashboard(SiteDocument $site) {
    $hasCriticalIssue = $site->hasCriticalIssues();
    $modulesNeedUpdate = array();
    foreach ($site->getModules() as $siteModule) {
      if (!isset($siteModule['latestVersion'])) {
        continue;
      }
      if ($siteModule['version'] == $siteModule['latestVersion']) {
        continue;
      }
      if (is_null($siteModule['version'])) {
        continue;
      }

      if ($siteModule['isSecurity']) {
        $hasCriticalIssue = TRUE;
      }

      $modulesNeedUpdate[] = $siteModule;
    }

    // Don't add the site to the dashboard if there are no critical issues.
    if (!$hasCriticalIssue) {
      return false;
    }

    /** @var DashboardDocument $dashboard */
    $dashboard = $this->makeNewItem();
    $dashboard->setName($site->getName());
    $dashboard->setSiteId($site->getId());
    $dashboard->setUrl($site->getUrl());
    $dashboard->setCoreVersion($site->getCoreVersion(), $site->getLatestCoreVersion(), $site->getIsSecurityCoreVersion());
    $dashboard->setHasCriticalIssue($hasCriticalIssue);
    $dashboard->setAdditionalIssues($site->getAdditionalIssues());
    $dashboard->setModules($modulesNeedUpdate);

    $this->saveDocument($dashboard);

    return true;
  }

}
