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
    return empty($this->latestVersion[$version]['recommended']['version']) ? 0 : $this->latestVersion[$version]['recommended']['version'];
  }

  /**
   * @param $version
   *
   * @return string
   */
  public function getOtherVersionByVersion($version) {
    return empty($this->latestVersion[$version]['other']['version']) ? 0 : $this->latestVersion[$version]['other']['version'];
  }

  /**
   * Sets the latest version information for the module.
   *
   * The version information includes the 'recommended' and 'other' release
   * versions (if they are available) as well as if each is a security update
   * or not.
   *
   * @param int $majorVersion
   * @param array $version
   */
  public function setLatestVersion($majorVersion, $version = array()) {
    $this->latestVersion[$majorVersion] = $version;
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
  public function addSite($siteId, $url, $version) {
    $moduleSites = $this->getSites();
    $moduleSites[] = array(
      'id' => $siteId,
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
    $recommendedVersion = $this->getLatestVersionByVersion($majorVersion);
    $otherVersion = $this->getOtherVersionByVersion($majorVersion);

    $latestVersion = self::getRelevantLatestVersion($version, $otherVersion, TRUE);
    if (!$latestVersion) {
      $latestVersion = $recommendedVersion;
    }
    return $version == $latestVersion;
  }

  public static function getRelevantLatestVersion($version, $otherVersion = 0, $compareFullVersions = FALSE) {
    if ($otherVersion > 0) {
      preg_match('/([1-9]).x-([0-9]+).([a-z0-9\-]+)/', $version, $versionMatches);
      //printf('<pre>version: %s</pre>', print_r($versionMatches, true));
      preg_match('/([1-9]).x-([0-9]+).([a-z0-9\-]+)/', $otherVersion, $otherMatches);
      //printf('<pre>other: %s</pre>', print_r($otherMatches, true));
      //preg_match('/([1-9]).x-([1-9]+).([a-z0-9\-])/', $recommendedVersion, $recommendedMatches);
      //printf('<pre>%s</pre>', print_r($recommendedMatches, true));
      //print "<br>$version, $otherVersion <br>";

      //print "$otherMatches[2] == $versionMatches[2]<br>";
      if ($otherMatches[1] == $versionMatches[1] && $otherMatches[2] == $versionMatches[2]) {
        if ($compareFullVersions) {
          return ($otherMatches[3] == $versionMatches[3]) ? $otherVersion : FALSE;
        }
        else {
          return $otherVersion;
        }
      }
    }

    return FALSE;
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