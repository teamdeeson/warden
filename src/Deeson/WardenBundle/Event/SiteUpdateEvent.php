<?php

/**
 * @file
 * An event which fires when a site asks to be updated and after that
 * update request has been verified.
 */

namespace Deeson\WardenBundle\Event;

use Deeson\WardenBundle\Document\SiteDocument;

class SiteUpdateEvent extends SiteEvent {

  /**
   * @var
   */
  protected $data;

  /**
   * @param SiteDocument $site
   * @param $data
   */
  public function __construct(SiteDocument $site, $data) {
    parent::__construct($site);
    $this->data = $data;
  }

  /**
   * @return
   */
  public function getData() {
    return $this->data;
  }
}
