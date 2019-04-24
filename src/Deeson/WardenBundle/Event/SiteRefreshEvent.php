<?php

/**
 * @file
 * An event which fires when an administrator requests a site to be refreshed.
 */

namespace Deeson\WardenBundle\Event;

class SiteRefreshEvent extends SiteEvent {

  const NOTICE = 1;
  const WARNING = 2;

  /**
   * @var array
   */
  protected $message = [];

  /**
   * Get the current messages about this event.
   *
   * @todo refactor for specific methods for each message type.
   *
   * @param int $type
   *
   * @return string
   */
  public function getMessage($type) {
    return implode(', ', $this->message[$type]);
  }

  /**
   * Add a message to this event.
   *
   * @todo refactor for specific methods for each message type.
   *
   * @param int $type
   * @param string $message
   */
  public function addMessage($message, $type = self::NOTICE) {
    $this->message[$type][] = $message;
  }

  /**
   * Determine if this event has a message attached to it.
   *
   * @todo refactor for specific methods for each message type.
   *
   * @param int $type
   *
   * @return bool
   */
  public function hasMessage($type) {
    return !empty($this->message[$type]);
  }

}
