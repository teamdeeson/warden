<?php

namespace Deeson\WardenBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(
 *     collection="modules"
 * )
 */
class ModuleDocument extends BaseDocument {

  /** Module version types */
  const MODULE_VERSION_TYPE_RECOMMENDED = 'recommended';
  const MODULE_VERSION_TYPE_OTHER = 'other';

  /** Module status' */
  const MODULE_PROJECT_STATUS_PUBLISHED = 'published';
  const MODULE_PROJECT_STATUS_UNSUPPORTED = 'unsupported';

  /**
   * @Mongodb\Field(type="string")
   */
  protected $name;

  /**
   * @Mongodb\Field(type="string")
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
   * @Mongodb\Field(type="string")
   */
  protected $projectStatus = '';

  /**
   * @var int
   */
  protected $usagePercentage;

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
    return empty($this->latestVersion[$version][self::MODULE_VERSION_TYPE_RECOMMENDED]['version']) ? 0 :
      $this->latestVersion[$version][self::MODULE_VERSION_TYPE_RECOMMENDED]['version'];
  }

  /**
   * @param $version
   *
   * @return string
   */
  public function getOtherVersionByVersion($version) {
    return empty($this->latestVersion[$version][self::MODULE_VERSION_TYPE_OTHER]['version']) ? 0 :
      $this->latestVersion[$version][self::MODULE_VERSION_TYPE_OTHER]['version'];
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
   * @param $siteId
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
   * Updates a site within the module with its latest version information.
   *
   * @param $siteId
   * @param $version
   */
  public function updateSite($siteId, $version) {
    $moduleSites = $this->getSites();
    foreach ($moduleSites as $key => $site) {
      if ($site['id'] == $siteId) {
        $moduleSites[$key]['version'] = $version;
        break;
      }
    }
    $this->setSites($moduleSites);
  }

  /**
   * Remove the site from the modules.
   *
   * @param $siteId
   */
  public function removeSite($siteId) {
    $moduleSites = $this->getSites();
    foreach ($moduleSites as $key => $site) {
      if ($site['id'] == $siteId) {
        unset($moduleSites[$key]);
        break;
      }
    }
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
   * @return int
   */
  public function getUsagePercentage() {
    return $this->usagePercentage;
  }

  /**
   * @param int $sitesTotalCount
   */
  public function setUsagePercentage($sitesTotalCount) {
    $this->usagePercentage = ($sitesTotalCount < 1) ? 0 : number_format($this->getSiteCount() / $sitesTotalCount * 100, 2);
  }

  /**
   * @return string
   */
  public function getProjectStatus() {
    return $this->projectStatus;
  }

  /**
   * @param string $projectStatus
   */
  public function setProjectStatus($projectStatus) {
    $this->projectStatus = $projectStatus;
  }

  public function isPublished() {
    return strtolower($this->projectStatus) == self::MODULE_PROJECT_STATUS_PUBLISHED;
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

  /**
   * Get the latest relevant version.
   *
   * @param $version
   * @param int $otherVersion
   * @param bool $compareFullVersions
   *
   * @return bool|int
   */
  public static function getRelevantLatestVersion($version, $otherVersion = 0, $compareFullVersions = FALSE) {
    if ($otherVersion > 0) {
      $versionMatches = self::getVersionInfo($version);
      //printf('<pre>version: %s</pre>', print_r($versionMatches, true));
      $otherMatches = self::getVersionInfo($otherVersion);
      //printf('<pre>other: %s</pre>', print_r($otherMatches, true));
      //print "<br>$version, $otherVersion <br>";

      if ($otherMatches['major'] == $versionMatches['major'] && $otherMatches['minor'] == $versionMatches['minor']) {
        if ($compareFullVersions) {
          return ($otherMatches['other'] == $versionMatches['other']) ? $otherVersion : FALSE;
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
    $info = self::getVersionInfo($version);
    return $info['major'];
  }

  /**
   * This gets the different version information and returns it as a keyed array.
   *
   * Module version number are in the following formats:
   *  7.x-1.3
   *  7.x-2.0-(alpha|beta|rc-0...|?)
   *  7.x-2.0+8-dev (dev release)
   *
   * The returned array has the following keys for a value of 7.x-2.0-beta4:
   *   'major' - the major version number (e.g. 7)
   *   'minor' - the minor version (e.g. 2)
   *   'other' - the other minor version (e.g. 0)
   *   'extra' - any extra version info (e.g. -beta4).  This defaults to NULL if there is no value
   *
   * @param string $version
   *
   * @return array
   *   Returns a keys array of each of the version information.
   */
  public static function getVersionInfo($version) {
    preg_match('/([0-9]+).x-([0-9]+).([0-9\.x]+)([a-z0-9\-+]+)?/', $version, $matches);

    // Standard version number regex doesn't match, probably Drupal release.
    if (count($matches) < 1) {
      preg_match('/([0-9]).([0-9]+)([a-z0-9\-+]+)?/', $version, $matches);
    }

    return array(
      'major' => (isset($matches[1])) ? $matches[1] : NULL,
      'minor' => (isset($matches[2])) ? $matches[2] : NULL,
      'other' => (isset($matches[3])) ? $matches[3] : NULL,
      'extra' => (isset($matches[4])) ? $matches[4] : NULL,
    );
  }

  /**
   * Decides whether the current version is the same as the latest version.
   *
   * @param array $moduleData
   *   The array of module data.
   *
   * @return bool
   *   Returns true if the version numbers match, otherwise false.
   */
  public static function isLatestVersion($moduleData) {
    return $moduleData['version'] == $moduleData['latestVersion'];
  }

  /**
   * Decides whether the current and latest main version numbers are the same.
   *
   * This has based upon the standard module array as stored against each site,
   * which contains the following keys: name, version, latestVersion, isSecurity.
   *
   * Module version number are in the following formats:
   *  7.x-1.3
   *  7.x-2.0-(alpha|beta|rc-0...|?)
   *  7.x-2.0+8-dev (dev release)
   *
   * For comparing the version numbers we are not worrying about the alpha/beta/dev
   * release data, just the major/ minor version numbers.
   *
   * @param array $moduleData
   *   The array of module data.
   *
   * @return bool
   *   Returns true if the version numbers match, otherwise false.
   */
  public static function versionsEqual($moduleData) {
    if (!isset($moduleData['latestVersion'])) {
      return FALSE;
    }

    $versionInfo = self::getVersionInfo($moduleData['version']);
    $versionNumber = sprintf('%d.x-%d.%d', $versionInfo['major'], $versionInfo['minor'], $versionInfo['other']);
    $latestVersionInfo = self::getVersionInfo($moduleData['latestVersion']);
    $latestVersionNumber = sprintf('%d.x-%d.%d', $latestVersionInfo['major'], $latestVersionInfo['minor'], $latestVersionInfo['other']);
    if ($versionNumber === '0.x-0.0' || $latestVersionNumber === '0.x-0.0') {
      return FALSE;
    }
    return $versionNumber == $latestVersionNumber;
  }

  /**
   * @param string $version
   *   The version number to check.
   *
   * @return bool
   */
  public static function isDevRelease($version) {
    $moduleVersionInfo = self::getVersionInfo($version);
    return isset($moduleVersionInfo['extra']) && strstr($moduleVersionInfo['extra'], 'dev') !== FALSE;
  }

}
