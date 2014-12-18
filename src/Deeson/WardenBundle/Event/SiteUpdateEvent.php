<?php

/**
 * @file
 * An event which fires when a site asks to be updated and after that
 * update request has been verified.
 */

namespace Deeson\WardenBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Deeson\WardenBundle\Document\SiteDocument;

class SiteUpdateEvent extends Event {

  /**
   * @var SiteDocument
   */
  protected $site;

  /**
   * @var
   */
  protected $data;

  /**
   * @param SiteDocument $site
   * @param $data
   */
  public function __construct(SiteDocument $site, $data) {
    $this->site = $site;
    $this->data = $data;
  }

  /**
   * @return SiteDocument
   */
  public function getSite() {
    return $this->site;
  }

  /**
   * @return
   */
  public function getData() {
    return $this->data;
  }
}