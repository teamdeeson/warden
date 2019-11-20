<?php

/**
 * @file
 * An event which fires when rendering the details of a site.
 */

namespace Deeson\WardenBundle\Event;

class SiteListEvent extends SiteEvent {

  /**
   * @var string
   */
  protected $siteTypeLogoPath = null;

  /**
   * @var string
   */
  protected $siteIssues = null;

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
   * @return string
   */
  public function getSiteIssues() {
    return $this->siteIssues;
  }

  /**
   * @param string $siteIssues
   */
  public function setSiteIssues($siteIssues) {
    $this->siteIssues = $siteIssues;
  }

}
