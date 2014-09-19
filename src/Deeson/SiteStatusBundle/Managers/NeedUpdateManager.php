<?php

namespace Deeson\SiteStatusBundle\Managers;

use Deeson\SiteStatusBundle\Document\NeedUpdateDocument;

class NeedUpdateManager extends BaseManager {

  /**
   * @return string
   *   The type of this manager.
   *   e.g. 'NeedUpdateDocument'
   */
  public function getType() {
    return 'NeedUpdateDocument';
  }

  /**
   * Create a new empty type of the object.
   *
   * @return NeedUpdateDocument
   */
  public function makeNewItem() {
    return new NeedUpdateDocument();
  }

}