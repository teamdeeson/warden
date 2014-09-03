<?php

namespace Deeson\SiteStatusBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(
 *     collection="modules"
 * )
 */
class Module extends BaseDocument {

  /**
   * @Mongodb\String
   */
  protected $name;

  /**
   * @Mongodb\String
   */
  protected $projectName;

  /**
   * @Mongodb\Float
   */
  protected $latestVersion;

  /**
   * @Mongodb\hash
   */
  protected $sites;

  /**
   * @return mixed
   */
  public function getName() {
    return empty($this->name) ? 'UNKNOWN' : $this->name;
  }

  /**
   * @param mixed $name
   */
  public function setName($name) {
    $this->name = $name;
  }

  /**
   * @return mixed
   */
  public function getProjectName() {
    return $this->projectName;
  }

  /**
   * @param mixed $projectName
   */
  public function setProjectName($projectName) {
    $this->projectName = $projectName;
  }

  /**
   * @return mixed
   */
  public function getLatestVersion() {
    return empty($this->latestVersion) ? '-' : $this->latestVersion;
  }

  /**
   * @param mixed $latestVersion
   */
  public function setLatestVersion($latestVersion) {
    $this->latestVersion = $latestVersion;
  }

  /**
   * @return mixed
   */
  public function getSites() {
    return $this->sites;
  }

  /**
   * @param mixed $sites
   */
  public function setSites($sites) {
    $this->sites = $sites;
  }

  /**
   * Add new site to the list of sites for this module.
   *
   * @param $url
   * @param $version
   */
  public function addSite($url, $version) {
    $moduleSites = $this->getSites();
    $moduleSites[] = array(
      'url' => $url,
      'version' => $version
    );
    $this->setSites($moduleSites);
  }

  /**
   * Get the count of the number of sites.
   *
   * @return int
   */
  public function getSiteCount() {
    return count($this->sites);
  }

}