<?php

/**
 * @file
 * An event which fires when a site is added to the dashboard.
 */

namespace Deeson\WardenBundle\Event;

use Deeson\WardenBundle\Document\SiteDocument;

class DashboardAddSiteEvent extends SiteEvent {

  /**
   * @var array
   */
  protected $issues = [];

  /**
   * @param SiteDocument $site
   *   The site object
   */
  public function __construct(SiteDocument $site) {
    parent::__construct($site);
  }

  /**
   * Add issues to the list of issues for the site.
   *
   * @param array $issues
   */
  public function setIssues($issues) {
    $this->issues = array_merge($this->issues, $issues);
  }

  /**
   * Gets the array of issues for the site.
   *
   * @return array
   */
  public function getIssues() {
    return $this->issues;
  }

}
