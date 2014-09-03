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
  protected $isNew;

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
   * @Mongodb\Hash
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
    return (empty($this->coreVersion['current'])) ? 'Not Imported Yet!' : $this->coreVersion['current'];
  }

  /**
   * @param mixed $coreVersion
   */
  public function setCoreVersion($coreVersion) {
    $this->coreVersion['current'] = $coreVersion;
  }

  /**
   * @return mixed
   */
  public function getLatestCoreVersion() {
    return (empty($this->coreVersion['latest'])) ? 'Not Imported Yet!' : $this->coreVersion['latest'];
  }

  /**
   * @param mixed $latestVersion
   */
  public function setLatestCoreVersion($latestVersion) {
    $this->coreVersion['latest'] = $latestVersion;
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
          'latest' => $version['version'],
        ),*/
      );
    }
    $this->modules = $moduleList;
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

  public function compareCoreVersion() {
    return is_float($this->getCoreVersion()) && $this->getCoreVersion() == $this->getLatestCoreVersion();
  }

}