<?php

namespace Deeson\WardenBundle\Managers;

use Deeson\WardenBundle\Document\SiteHaveIssueDocument;

class SiteHaveIssueManager extends BaseManager {

  /**
   * @return string
   *   The type of this manager.
   *   e.g. 'SiteHaveIssueDocument'
   */
  public function getType() {
    return 'SiteHaveIssueDocument';
  }

  /**
   * Create a new empty type of the object.
   *
   * @return SiteHaveIssueDocument
   */
  public function makeNewItem() {
    return new SiteHaveIssueDocument();
  }

}