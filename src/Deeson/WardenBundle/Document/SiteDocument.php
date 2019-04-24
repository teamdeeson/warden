<?php

namespace Deeson\WardenBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(
 *     collection="sites"
 * )
 */
class SiteDocument extends BaseDocument {

  const TYPE_DEFAULT = 'drupal';

  /**
   * @Mongodb\Field(type="string")
   */
  protected $name;

  /**
   * @Mongodb\Field(type="boolean")
   */
  protected $isNew = TRUE;

  /**
   * @Mongodb\Field(type="string")
   */
  protected $url;

  /**
   * @Mongodb\Field(type="string")
   */
  protected $wardenToken;

  /**
   * @Mongodb\Field(type="string")
   */
  protected $wardenEncryptToken;

  /**
   * @Mongodb\Field(type="string")
   */
  protected $authUser;

  /**
   * @Mongodb\Field(type="string")
   */
  protected $authPass;

  /**
   * @Mongodb\Field(type="boolean")
   */
  protected $hasCriticalIssue;

  /**
   * @Mongodb\Field(type="hash")
   */
  protected $additionalIssues;

  /**
   * @Mongodb\Field(type="string")
   */
  protected $lastSuccessfulRequest;

  /**
   * @Mongodb\Field(type="string")
   */
  protected $type;

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
   * @return string
   */
  public function getWardenToken() {
    return $this->wardenToken;
  }

  /**
   * @param string $wardenToken
   */
  public function setWardenToken($wardenToken) {
    $this->wardenToken = $wardenToken;
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
   * @return mixed
   */
  public function getAuthPass() {
    return !empty($this->authPass) ? $this->authPass : NULL;
  }

  /**
   * @param mixed $authPass
   */
  public function setAuthPass($authPass) {
    $this->authPass = $authPass;
  }

  /**
   * @return mixed
   */
  public function getAuthUser() {
    return !empty($this->authUser) ? $this->authUser : NULL;
  }

  /**
   * @param mixed $authUser
   */
  public function setAuthUser($authUser) {
    $this->authUser = $authUser;
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
    // @todo format of these issues??
    $this->additionalIssues = array_merge($this->getAdditionalIssues(), $additionalIssues);
  }

  /**
   * @return mixed
   */
  public function getType() {
    return !empty($this->type) ? $this->type : self::TYPE_DEFAULT;
  }

  /**
   * @return mixed
   */
  public function getTypeRaw() {
    return $this->type;
  }

  /**
   * @param mixed $type
   */
  public function setType($type) {
    if (empty($type)) {
      $type = self::TYPE_DEFAULT;
    }

    $this->type = $type;
  }

  /**
   * Sets a default type for old versions where is isn't set when the site is added.
   */
  public function setDefaultType() {
    $this->setType(NULL);
  }

  /**
   * @return mixed
   */
  public function getLastSuccessfulRequest() {
    return !empty($this->lastSuccessfulRequest) ? $this->lastSuccessfulRequest : 'No request completed yet';
  }

  /**
   * Set the last successful datetime.
   */
  public function setLastSuccessfulRequest() {
    $this->lastSuccessfulRequest = date('d/m/Y H:i:s');
  }

  /**
   * Checks if the site has been updated within the last 24 hours.
   *
   * @return bool
   *   True if the site not been updated recently.
   */
  public function hasNotUpdatedRecently() {
    if (empty($this->lastSuccessfulRequest)) {
      return false;
    }

    $timestamp = \DateTime::createFromFormat('d/m/Y H:i:s', $this->lastSuccessfulRequest)->getTimestamp();
    $diff = time() - $timestamp;
    return $diff > (60 * 60 * 24);
  }

}
