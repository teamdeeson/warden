<?php

namespace Deeson\SiteStatusBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(
 *     collection="sites"
 * )
 */
class Site extends BaseDocument {

  /**
   * @Mongodb\String
   */
  protected $name;

  /**
   * @Mongodb\Boolean
   */
  protected $isNew = TRUE;

  /**
   * @Mongodb\String
   */
  protected $url;

  /**
   * @Mongodb\Hash
   */
  protected $coreVersion;

  /**
   * @Mongodb\String
   */
  protected $systemStatusToken;

  /**
   * @Mongodb\String
   */
  protected $systemStatusEncryptToken;

  /**
   * @Mongodb\Collection
   */
  protected $modules;

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
  public function getSystemStatusEncryptToken() {
    return $this->systemStatusEncryptToken;
  }

  /**
   * @param mixed $system_status_encrypt_token
   */
  public function setSystemStatusEncryptToken($system_status_encrypt_token) {
    $this->systemStatusEncryptToken = $system_status_encrypt_token;
  }

  /**
   * @return string
   */
  public function getSystemStatusToken() {
    return $this->systemStatusToken;
  }

  /**
   * @param string $systemStatusToken
   */
  public function setSystemStatusToken($systemStatusToken) {
    $this->systemStatusToken = $systemStatusToken;
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
   * @return mixed
   */
  public function getCoreVersion() {
    return (empty($this->coreVersion['current'])) ? '0' : $this->coreVersion['current'];
  }

  /**
   * @param mixed $coreVersion
   */
  public function setCoreVersion($coreVersion) {
    $majorRelease = Module::getMajorVersion($coreVersion);
    $this->coreVersion = array(
      'release' => $majorRelease,
      'current' => $coreVersion,
    );
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
   * @return mixed
   */
  public function getModules() {
    return $this->modules;
  }

  /**
   * @param mixed $modules
   */
  public function setModules($modules) {
    $moduleList = array();
    foreach ($modules as $name => $version) {
      $moduleList[] = array(
        'name' => $name,
        'version' => $version['version'],
        /*'version' => array(
          'current' => $version['version'],
          'latest' => '',
          'isSecurity' => 0,
        ),*/
      );
    }
    $this->modules = $moduleList;
  }

  /**
   * Gets a modules latest version for the site.
   *
   * @param $module
   *
   * @return string
   */
  public function getModuleLatestVersion($module) {
    return (!isset($module['latestVersion'])) ? '' : $module['latestVersion'];
  }

  /**
   * Returns if the provided module has a security release.
   *
   * @param $module
   *
   * @return boolean
   */
  public function getModuleIsSecurity($module) {
    return (!isset($module['isSecurity'])) ? FALSE : $module['isSecurity'];
  }

  /**
   * Sets the latest versions of each of the modules for the site.
   *
   * @param $moduleLatestVersions
   */
  public function setModulesLatestVersion($moduleLatestVersions) {
    $siteModuleList = $this->getModules();
    foreach ($siteModuleList as $key => $module) {
      if (!isset($moduleLatestVersions[$module['name']])) {
        continue;
      }
      $moduleVersions = $moduleLatestVersions[$module['name']];

      $versionType = 'recommended';
      if (isset($moduleVersions['other'])) {
        $latestVersion = Module::getRelevantLatestVersion($module['version'], $moduleVersions['other']['version']);
        if ($latestVersion) {
          $versionType = 'other';
        }
      }

      $siteModuleList[$key]['latestVersion'] = $moduleVersions[$versionType]['version'];
      $siteModuleList[$key]['isSecurity'] = $moduleVersions[$versionType]['isSecurity'];
    }
    $this->modules = $siteModuleList;
  }

  /**
   * @return mixed
   */
  public function getIsNew() {
    return $this->isNew;
  }

  /**
   * @param boolean $isNew
   */
  public function setIsNew($isNew) {
    $this->isNew = $isNew;
  }

  /**
   * Compare the current core version with the latest core version.
   *
   * @return bool
   */
  public function compareCoreVersion() {
    return is_float($this->getCoreVersion()) && $this->getCoreVersion() == $this->getLatestCoreVersion();
  }

}