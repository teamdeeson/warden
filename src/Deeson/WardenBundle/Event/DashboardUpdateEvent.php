<?php

/**
 * @file
 * An event which fires when an administrator requests a site to be refreshed.
 */

namespace Deeson\WardenBundle\Event;

use Deeson\WardenBundle\Document\SiteDocument;

class DashboardUpdateEvent extends SiteEvent {

  /**
   * @var boolean
   */
  protected $forceDelete;

  /**
   * @param SiteDocument $site
   *   The site object
   * @param bool $forceDelete
   *   If true will only remove the site from the dashboard.
   */
  public function __construct(SiteDocument $site, $forceDelete = FALSE) {
    parent::__construct($site);
    $this->forceDelete = $forceDelete;
  }

  /**
   * @return boolean
   */
  public function isForceDelete() {
    return $this->forceDelete;
  }
}
