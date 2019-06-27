<?php

namespace Deeson\WardenThirdPartyLibraryBundle\Managers;

use Deeson\WardenThirdPartyLibraryBundle\Document\SiteThirdPartyLibraryDocument;

class SiteThirdPartyLibraryManager extends ThirdPartyBaseManager {

  /**
   * @return string
   *   The type of this manager.
   *   e.g. 'LibraryDocument'
   */
  public function getType() {
    return 'SiteThirdPartyLibraryDocument';
  }

  /**
   * Create a new empty type of the object.
   *
   * @return SiteThirdPartyLibraryDocument
   */
  public function makeNewItem() {
    return new SiteThirdPartyLibraryDocument();
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
