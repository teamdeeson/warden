<?php

namespace Deeson\SiteStatusBundle\Managers;

use Deeson\SiteStatusBundle\Document\Site;

class SiteManager extends BaseManager {

  /**
   * Return boolean of whether a site with this URL already exists.
   *
   * @param string $url
   *
   * @return bool
   */
  public function urlExists($url) {
    $result = $this->getDocumentsBy(array('url' => $url));
    return $result->count() > 0;
  }

  /**
   * @return string
   *   The type of this manager.
   *   e.g. 'Site'
   */
  public function getType() {
    return 'Site';
  }

  /**
   * Create a new empty type of the object.
   *
   * @return Site
   */
  public function makeNewItem() {
    return new Site();
  }

  /**
   *
   *
   * @param int $version
   *
   * @return array
   */
  public function getAllByVersion($version) {
    return $this->getDocumentsBy(array('coreVersion.release' => $version));
    //'/^' . $version . '.x.*/'
  }

  /**
   * Gets the list of major release versions that are being used on registered sites.
   *
   * @return array
   */
  public function getAllMajorVersionReleases() {
    $qb = $this->createIndexQuery();
    $qb->distinct('coreVersion.release');

    $cursor = $qb->getQuery()->execute();

    $results = array();
    foreach ($cursor as $result) {
      $results[] = $result;
    }

    return $results;
  }
}