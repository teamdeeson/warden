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
}
