<?php

/**
 * @file
 * An event which fires when an administrator requests a site to be refreshed.
 */

namespace Deeson\WardenBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class CronEvent extends Event {

  /**
   * @var array
   */
  protected $sites;

  /**
   * @param array $sites
   */
  public function __construct(array $sites) {
    $this->sites = $sites;
  }

  /**
   * @return array
   */
  public function getSites() {
    return $this->sites;
  }
}
