<?php

namespace Deeson\SiteStatusBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(
 *     collection="sites-have-issues"
 * )
 */
class SiteHaveIssueDocument extends BaseDocument {

  /**
   * @Mongodb\String
   */
  protected $name;

  /**
   * @Mongodb\ObjectId
   */
  protected $siteId;

  /**
   * @Mongodb\String
   */
  protected $url;

  /**
   * @Mongodb\Hash
   */
  protected $coreVersion;

  /**
   * @Mongodb\Collection
   */
  protected $modules;

  /**
   * @return mixed
   */
  public function getCoreVersion() {
    return (empty($this->coreVersion['current'])) ? '0' : $this->coreVersion['current'];
  }

  /**
   * @param $version
   * @param $latestVersion
   * @param $isSecurity
   */
  public function setCoreVersion($version, $latestVersion, $isSecurity) {
    $this->coreVersion = array(
      'current' => $version,
      'latest' => $latestVersion,
      'isSecurity' => $isSecurity,
    );
  }

  /**
   * @return mixed
   */
  public function getLatestCoreVersion() {
    return (empty($this->coreVersion['latest'])) ? '0' : $this->coreVersion['latest'];
  }

  /**
   * @return boolean
   */
  public function getIsSecurityCoreVersion() {
    return (empty($this->coreVersion['isSecurity'])) ? FALSE : $this->coreVersion['isSecurity'];
  }

  /**
   * @return mixed
   */
  public function getModules() {
    return $this->modules;
  }

  /**
   * @param mixed $modules
   */
  public function setModules($modules) {
    $this->modules = $modules;
  }

  /**
   * @return mixed
   */
  public function getName() {
    return $this->name;
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
   * @return mixed
   */
  public function getUrl() {
    return $this->url;
  }

  /**
   * @param mixed $url
   */
  public function setUrl($url) {
    $this->url = $url;
  }

}