<?php

namespace Deeson\WardenBundle\Managers;

use Deeson\WardenBundle\Document\DashboardDocument;
use Deeson\WardenBundle\Document\SiteDocument;

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
   * Adds the site to the dashboard if needed.
   *
   * @param \Deeson\WardenBundle\Document\SiteDocument $site
   *
   * @return bool
   *   True if the site has been added otherwise false.
   */
  public function addSiteToDashboard(SiteDocument $site) {
    $hasCriticalIssue = $site->hasCriticalIssues();
    $isModuleSecurityUpdate = FALSE;
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
        $isModuleSecurityUpdate = TRUE;
        $hasCriticalIssue = TRUE;
      }

      $modulesNeedUpdate[] = $siteModule;
    }

    if ($site->getLatestCoreVersion() == $site->getCoreVersion() && !$isModuleSecurityUpdate && !$hasCriticalIssue) {
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
