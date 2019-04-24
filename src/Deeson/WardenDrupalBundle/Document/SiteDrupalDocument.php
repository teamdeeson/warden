<?php

namespace Deeson\WardenDrupalBundle\Document;

use Deeson\WardenBundle\Document\BaseDocument;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(
 *     collection="sites_drupal"
 * )
 */
class SiteDrupalDocument extends BaseDocument {

  const TYPE_DRUPAL = 'drupal';

  /**
   * @Mongodb\Field(type="string")
   */
  protected $siteId;

  /**
   * @Mongodb\Field(type="hash")
   */
  protected $coreVersion;

  /**
   * @return mixed
   */
  public function getSiteId() {
    return $this->siteId;
  }

  /**
   * @param mixed $siteId
   */
  public function setSiteId($siteId) {
    $this->siteId = $siteId;
  }

  /**
   * @return string
   */
  public function getTypeImagePath() {
    if ($this->getCoreVersion() < 8) {
      return 'bundles/deesonwardendrupal/images/drupal7-logo.png';
    }

    return 'bundles/deesonwardendrupal/images/drupal8-logo.png';
  }

  /**
   * @return mixed
   */
  public function getCoreVersion() {
    return (empty($this->coreVersion['current'])) ? '0' : $this->coreVersion['current'];
  }

  /**
   * @param mixed $coreVersion
   */
  public function setCoreVersion($coreVersion) {
    $majorRelease = DrupalModuleDocument::getMajorVersion($coreVersion);
    if (!isset($this->coreVersion)) {
      $this->coreVersion = array();
    }
    /*$this->coreVersion = array_merge(array(
      'release' => $majorRelease,
      'current' => $coreVersion,
    ));*/
    $this->coreVersion['release'] = $majorRelease;
    $this->coreVersion['current'] = $coreVersion;
  }

  /**
   * @return mixed
   */
  public function getCoreReleaseVersion() {
    return (empty($this->coreVersion['release'])) ? '0' : $this->coreVersion['release'];
  }

  /**
   * @return mixed
   */
  public function getLatestCoreVersion() {
    return (empty($this->coreVersion['latest'])) ? '0' : $this->coreVersion['latest'];
  }

  /**
   * @param mixed $latestVersion
   * @param boolean $isSecurity
   */
  public function setLatestCoreVersion($latestVersion, $isSecurity = FALSE) {
    /*$this->coreVersion += array(
      'latest' => $latestVersion,
      'isSecurity' => $isSecurity,
    );*/
    $this->coreVersion['latest'] = $latestVersion;
    $this->coreVersion['isSecurity'] = $isSecurity;
  }

  /**
   * @return boolean
   */
  public function getIsSecurityCoreVersion() {
    return (empty($this->coreVersion['isSecurity'])) ? FALSE : $this->coreVersion['isSecurity'];
  }

  /**
   * Compare the current core version with the latest core version.
   *
   * @return bool
   *   TRUE if the current core version is less than the latest core version.
   */
  public function hasOlderCoreVersion() {
    return $this->getCoreVersion() < $this->getLatestCoreVersion();
  }

}
