<?php

/**
 * @file
 * An event which fires when an administrator requests a site to be refreshed.
 */

namespace Deeson\WardenBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Deeson\WardenBundle\Document\SiteDocument;

abstract class SiteEvent extends Event {

  /**
   * @var SiteDocument
   */
  protected $site;

  /**
   * @param SiteDocument $site
   */
  public function __construct(SiteDocument $site) {
    $this->site = $site;
  }

  /**
   * @return SiteDocument
   */
  public function getSite() {
    return $this->site;
  }
}
