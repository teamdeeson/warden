<?php

namespace Deeson\WardenBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(
 *     collection="dashboard"
 * )
 */
class DashboardDocument extends BaseDocument {

  /**
   * @Mongodb\Field(type="string")
   */
  protected $name;

  /**
   * @Mongodb\ObjectId
   */
  protected $siteId;

  /**
   * @Mongodb\Field(type="string")
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
   * @Mongodb\Boolean
   */
  protected $hasCriticalIssue;

  /**
   * @Mongodb\Hash
   */
  protected $additionalIssues;

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
    return (empty($this->name)) ? '[Site Name]' : $this->name;
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

  /**
   * @return boolean
   */
  public function getHasCriticalIssue() {
    return $this->hasCriticalIssue;
  }

  /**
   * @param boolean $hasCriticalIssue
   */
  public function setHasCriticalIssue($hasCriticalIssue) {
    $this->hasCriticalIssue = $hasCriticalIssue;
  }

  /**
   * @return mixed
   */
  public function getAdditionalIssues() {
    return !empty($this->additionalIssues) ? $this->additionalIssues : array();
  }

  /**
   * @param mixed $additionalIssues
   */
  public function setAdditionalIssues($additionalIssues) {
    // @todo format of these issues - same as how it is stored in SiteDocument??
    $this->additionalIssues = $additionalIssues;
  }

}
