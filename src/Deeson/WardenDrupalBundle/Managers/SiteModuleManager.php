<?php

namespace Deeson\WardenDrupalBundle\Managers;

use Deeson\WardenDrupalBundle\Document\SiteModuleDocument;

class SiteModuleManager extends DrupalBaseManager {

  /**
   * @return string
   *   The type of this manager.
   */
  public function getType() {
    return 'SiteModuleDocument';
  }

  /**
   * Create a new empty type of the object.
   *
   * @return SiteModuleDocument
   */
  public function makeNewItem() {
    return new SiteModuleDocument();
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
