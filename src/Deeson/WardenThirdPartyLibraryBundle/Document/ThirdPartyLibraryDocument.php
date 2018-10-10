<?php

namespace Deeson\WardenThirdPartyLibraryBundle\Document;

use Deeson\WardenBundle\Document\BaseDocument;
use Deeson\WardenBundle\Document\SiteDocument;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(
 *     collection="thirdpartylibrary"
 * )
 */
class ThirdPartyLibraryDocument extends BaseDocument {

  /**
   * @Mongodb\Field(type="string")
   */
  protected $name;

  /**
   * @Mongodb\Field(type="string")
   */
  protected $urlSafeName;

  /**
   * @Mongodb\Field(type="string")
   */
  protected $type;

  /**
   * @Mongodb\Field(type="collection")
   */
  protected $sites;

  /**
   * @var int
   */
  protected $usagePercentage;

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
    $this->setUrlSafeName();
  }

  /**
   * @return mixed
   */
  public function getUrlSafeName() {
    return $this->urlSafeName;
  }

  /**
   * @return mixed
   */
  public function getType() {
    return $this->type;
  }

  /**
   * @param mixed $type
   */
  public function setType($type) {
    $this->type = $type;
  }

  /**
   * @return mixed
   */
  public function getSites() {
    return !empty($this->sites) ? $this->sites : array();
  }

  /**
   * @param mixed $sites
   */
  public function setSites($sites) {
    $this->sites = $sites;
  }

  /**
   * @param SiteDocument $site
   * @param $version
   */
  public function addSite(SiteDocument $site, $version) {
    $librarySites = $this->getSites();
    $siteAlreadyRegistered = FALSE;
    foreach ($librarySites as $librarySite) {
      if ($librarySite['name'] === $site->getName()) {
        $siteAlreadyRegistered = TRUE;
        break;
      }
    }

    if (!$siteAlreadyRegistered) {
      $librarySites[] = array(
        'id' => $site->getId(),
        'name' => $site->getName(),
        'url' => $site->getUrl(),
        'version' => $version,
      );
    }
    $this->setSites($librarySites);
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
   * Sets the internal URL safe name which is used to identify the library.
   */
  protected function setUrlSafeName() {
    $this->urlSafeName = strtolower(str_replace(array(' ', '/'), '-', $this->getName()));
  }

}
