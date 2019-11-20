<?php

/**
 * @file
 * An event which fires when rendering the details of a site.
 */

namespace Deeson\WardenBundle\Event;

use Deeson\WardenBundle\Document\DashboardDocument;
use Symfony\Component\EventDispatcher\Event;

class DashboardListEvent extends Event {

  /**
   * @var DashboardDocument
   */
  protected $site;

  /**
   * @var string
   */
  protected $siteTypeLogoPath = null;

  /**
   * @var int
   */
  protected $siteIssuesCount = 0;

  /**
   * @param DashboardDocument $site
   */
  public function __construct(DashboardDocument $site) {
    $this->site = $site;
  }

  /**
   * @return DashboardDocument
   */
  public function getSite() {
    return $this->site;
  }

  /**
   * @return string
   */
  public function getSiteTypeLogoPath() {
    return $this->siteTypeLogoPath;
  }

  /**
   * @param string $logoPath
   */
  public function setSiteTypeLogoPath($logoPath) {
    $this->siteTypeLogoPath = $logoPath;
  }

  /**
   * @return int
   */
  public function getSiteIssuesCount() {
    return $this->siteIssuesCount;
  }

  /**
   * @param int $count
   */
  public function setSiteIssuesCount($count) {
    $this->siteIssuesCount = $count;
  }

}
