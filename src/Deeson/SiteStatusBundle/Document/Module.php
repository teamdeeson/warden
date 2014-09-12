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
   * @Mongodb\Boolean
   */
  protected $isNew = TRUE;

  /**
   * @Mongodb\Hash
   */
  protected $latestVersion;

  /**
   * @var string
   * @MongoDB\Collection
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
   * @param mixed $isNew
   */
  public function setIsNew($isNew) {
    $this->isNew = $isNew;
  }

  /**
   * @return mixed
   */
  public function getIsNew() {
    return $this->isNew;
  }

  /**
   * @return mixed
   */
  public function getLatestVersion() {
    return empty($this->latestVersion) ? array() : $this->latestVersion;
  }

  /**
   * @param $version
   *
   * @return string
   */
  public function getLatestVersionByVersion($version) {
    return empty($this->latestVersion[$version]['version']) ? '-' : $this->latestVersion[$version]['version'];
  }

  /**
   * Sets the latest version and if this is a security update or not.
   *
   * @param int $version
   * @param string $latestVersion
   * @param bool $security
   */
  public function setLatestVersion($version, $latestVersion = '', $security = FALSE) {
    $this->latestVersion[$version] = array(
      'version' => $latestVersion,
      'security' => $security,
    );
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
      'version' => $version,
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

  /**
   * Compare the supplied module version with the latest module version.
   *
   * @param string $version
   *
   * @return bool
   */
  public function compareVersion($version) {
    $majorVersion = self::getMajorVersion($version);
    return $version == $this->getLatestVersionByVersion($majorVersion);
  }

  /**
   * Get the major drupal version from the module version.
   *
   * @param $version
   *
   * @return string
   */
  public static function getMajorVersion($version) {
    return substr($version, 0, 1);
  }

}