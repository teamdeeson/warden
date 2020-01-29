<?php

namespace Deeson\WardenDrupalBundle\Managers;

use Deeson\WardenDrupalBundle\Document\SiteDrupalModuleDocument;

class SiteDrupalModuleManager extends DrupalBaseManager {

  /**
   * @return string
   *   The type of this manager.
   */
  public function getType() {
    return 'SiteDrupalModuleDocument';
  }

  /**
   * Create a new empty type of the object.
   *
   * @return SiteDrupalModuleDocument
   */
  public function makeNewItem() {
    return new SiteDrupalModuleDocument();
  }

  /**
   * @param \Deeson\WardenBundle\Document\SiteDocument $site
   *
   * @return null|object
   */
  public function findBySiteId($site) {
    return $this->getRepository()->findOneBy(array('siteId' => $site));
  }

  /**
   * Adds a flag for safe version.
   *
   * @param $siteId
   * @param $user
   * @param $module
   * @param $reason
   *
   * @throws \Exception
   */
  public function addSafeVersionFlag($siteId, $user, $module, $reason) {
    /** @var SiteDrupalModuleDocument $siteDrupal */
    $siteDrupal = $this->findBySiteId($siteId);
    $siteDrupal->addSafeVersionFlag($user, $module, $reason);
    $this->saveDocument($siteDrupal);
  }

  /**
   * @param $siteId
   * @param $moduleName
   *
   * @return string
   */
  public function getSafeVersionFlag($siteId, $moduleName) {
    /** @var SiteDrupalModuleDocument $siteDrupal */
    $siteDrupal = $this->findBySiteId($siteId);
    foreach ($siteDrupal->getModules() as $module) {
      if ($module['name'] !== $moduleName) {
        continue;
      }

      return array_pop($module['flag']['safeVersion']);
    }

    return null;
  }
}
