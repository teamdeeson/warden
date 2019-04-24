<?php

namespace Deeson\WardenBundle\Managers;

use Deeson\WardenBundle\Document\SiteDocument;
use Doctrine\ODM\MongoDB\MongoDBException;

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
   * Get the sites based upon a list of object ids.
   *
   * @param array $siteIds
   *   An array of site ids as a Mongo object id.
   * @param boolean $newOnly
   *   Whether this is to only process new sites.
   *
   * @return array
   *   An array of SiteDocuments.
   */
  public function getAllBySiteIds($siteIds, $newOnly) {
    try {
      /** @var \Doctrine\ODM\MongoDB\Query\Builder $qb */
      $qb = $this->createQueryBuilder();
      $qb->field('_id')->in($siteIds);

      if ($newOnly) {
        $qb->field('isNew')->equals(TRUE);
      }

      $cursor = $qb->getQuery()->execute();
      $results = array();
      foreach ($cursor as $result) {
        $results[] = $result;
      }

      return $results;
    }
    catch (MongoDBException $e) {
      return array();
    }
  }
}
