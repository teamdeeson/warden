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
   * @Mongodb\Field(type="object_id")
   */
  protected $siteId;

  /**
   * @Mongodb\Field(type="string")
   */
  protected $url;

  /**
   * @Mongodb\Field(type="string")
   */
  protected $type;

  /**
   * @Mongodb\Field(type="boolean")
   */
  protected $hasCriticalIssue;

  /**
   * @Mongodb\Field(type="hash")
   */
  protected $additionalIssues;

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
   * @return mixed
   */
  public function getType() {
    return !empty($this->type) ? $this->type : SiteDocument::TYPE_DEFAULT;
  }

  /**
   * @param mixed $type
   */
  public function setType($type) {
    $this->type = $type;
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
