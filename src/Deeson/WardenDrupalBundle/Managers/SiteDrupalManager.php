<?php

namespace Deeson\WardenDrupalBundle\Managers;

use Deeson\WardenDrupalBundle\Document\SiteDrupalDocument;
use Doctrine\ODM\MongoDB\MongoDBException;

class SiteDrupalManager extends DrupalBaseManager {

  /**
   * @return string
   *   The type of this manager.
   */
  public function getType() {
    return 'SiteDrupalDocument';
  }

  /**
   * Create a new empty type of the object.
   *
   * @return SiteDrupalDocument
   */
  public function makeNewItem() {
    return new SiteDrupalDocument();
  }

  /**
   * @param \Deeson\WardenBundle\Document\SiteDocument $site
   *
   * @return null|object
   */
  public function getBySiteId($site) {
    return $this->getRepository()->findOneBy(array('siteId' => $site));
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
  }

  /**
   * Gets the list of major release versions that are being used on registered sites.
   *
   * @return array
   *   An array of SiteDrupalDocuments.
   */
  public function getAllMajorVersionReleases() {
    try {
      /** @var \Doctrine\ODM\MongoDB\Query\Builder $qb */
      $qb = $this->createQueryBuilder();
      $qb->distinct('coreVersion.release');
      $qb->field('coreVersion.release')->notEqual('0');

      $cursor = $qb->getQuery()->execute();

      $results = [];
      foreach ($cursor as $result) {
        $results[] = $result;
      }
      sort($results);

      return $results;
    }
    catch (MongoDBException $e) {
      return array();
    }
  }
}
