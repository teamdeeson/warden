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
   * @Mongodb\Float
   */
  protected $latestVersion;

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
  public function getLatestVersion() {
    return empty($this->latestVersion) ? '-' : $this->latestVersion;
  }

  /**
   * @param mixed $latestVersion
   */
  public function setLatestVersion($latestVersion) {
    $this->latestVersion = $latestVersion;
  }

}