<?php

namespace Deeson\WardenBundle\Managers;

use Deeson\WardenBundle\Document\SiteDocument;

class SiteManager extends BaseManager {

  /**
   * Return boolean of whether a site with this URL already exists.
   *
   * @param string $url
   *
   * @return bool
   */
  public function urlExists($url) {
    return !empty($this->getDocumentsBy(array('url' => $url)));
  }

  /**
   * @return string
   *   The type of this manager.
   *   e.g. 'Site'
   */
  public function getType() {
    return 'SiteDocument';
  }

  /**
   * Create a new empty type of the object.
   *
   * @return SiteDocument
   */
  public function makeNewItem() {
    return new SiteDocument();
  }

  /**
   * Gets all the sites for a specific version.
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
    /** @var \Doctrine\ODM\MongoDB\Query\Builder $qb */
    $qb = $this->createQueryBuilder();
    $qb->distinct('coreVersion.release');
    $qb->field('coreVersion.release')->notEqual('0');
    //$qb->sort('coreVersion.release', 'ASC');

    $cursor = $qb->getQuery()->execute();

    $results = array();
    foreach ($cursor as $result) {
      $results[] = $result;
    }
    sort($results);

    return $results;
  }
}
