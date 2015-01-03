<?php

/**
 * @file
 * An event which fires when an administrator requests a site to be refreshed.
 */

namespace Deeson\WardenBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Deeson\WardenBundle\Document\SiteDocument;

class SiteEvent extends Event {

  /**
   * @var SiteDocument
   */
  protected $site;

  /**
   * @var string
   */
  protected $message = '';

  /**
   * @param SiteDocument $site
   */
  public function __construct(SiteDocument $site) {
    $this->site = $site;
  }

  /**
   * Get the current messages about this event.
   *
   * @return string
   */
  public function getMessage() {
    return $this->message;
  }

  /**
   * Add a message to this event.
   *
   * @param string $message
   */
  public function addMessage($message) {
    $this->message .= $message;
  }

  /**
   * Determine if this event has a message attached to it.
   *
   * @return bool
   */
  public function hasMessage() {
    return !empty($this->message);
  }

  /**
   * @return SiteDocument
   */
  public function getSite() {
    return $this->site;
  }
}