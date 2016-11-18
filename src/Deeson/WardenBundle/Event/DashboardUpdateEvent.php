<?php

/**
 * @file
 * An event which fires when an administrator requests a site to be refreshed.
 */

namespace Deeson\WardenBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Deeson\WardenBundle\Document\SiteDocument;

class DashboardUpdateEvent extends Event {

  /**
   * @var SiteDocument
   */
  protected $site;

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
    $this->site = $site;
    $this->forceDelete = $forceDelete;
  }

  /**
   * @return SiteDocument
   */
  public function getSite() {
    return $this->site;
  }

  /**
   * @return boolean
   */
  public function isForceDelete() {
    return $this->forceDelete;
  }
}
