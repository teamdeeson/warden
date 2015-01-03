<?php

/**
 * @file
 * A central place to store data about events.
 */

namespace Deeson\WardenBundle\Event;

final class WardenEvents {

  /**
   * The warden.site-update event is thrown each time a site asks to be updated
   * after the update request has been verified.
   *
   * The event listener receives an
   * Deeson\WardenBundle\Event\SiteUpdateEvent instance.
   *
   * @var string
   */
  const WARDEN_SITE_UPDATE = 'warden.site.update';

  /**
   * The warden.site-load event is thrown when Warden loads a site from the
   * database.
   */
  const WARDEN_SITE_LOAD = 'warden.site.load';

  /**
   * The warden.site.refresh event is thrown when an administrator requests for
   * a site to be updated.
   */
  const WARDEN_SITE_REFRESH = 'warden.site.refresh';
}