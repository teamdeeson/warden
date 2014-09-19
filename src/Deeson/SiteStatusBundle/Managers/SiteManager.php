<?php

namespace Deeson\SiteStatusBundle\Managers;

use Deeson\SiteStatusBundle\Document\SiteDocument;

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
    $qb = $this->createIndexQuery();
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

  /**
   * Gets all the sites that have a error against them.
   *
   * A site could have an error for any one of the following reasons:
   *
   *  - Core version is out of date and have security releases.
   *  - Module versions are out of date and have security releases.
   *
   * @return array
   * @throws \Doctrine\ODM\MongoDB\MongoDBException
   */
  public function getAllSitesWithErrors() {
    /** @var \Doctrine\ODM\MongoDB\Query\Builder $qb */
    /*$qb = $this->createIndexQuery();
    $qb->addAnd($qb->expr()->field('isNew')->equals(FALSE));
    $qb->addAnd($qb->expr()->field('coreVersion.current')->notEqual(''));
    $qb->addAnd($qb->expr()->field('coreVersion.latest')->exists(TRUE));
    $qb->addAnd($qb->expr()->field('coreVersion.latest')->notEqual(''));
    //$qb->addAnd($qb->expr()->field('coreVersion.current')->notEqual('coreVersion.latest'));
    //$qb->addOr(
      //$qb->addAnd($qb->expr()->field('coreVersion.release')->equals('6'))
      //  ->addAnd($qb->expr()->field('coreVersion.current')->notEqual('6.33'));
    //);
    //$qb->addOr(
      $qb->addAnd($qb->expr()->field('coreVersion.release')->equals('7'))
        ->addAnd($qb->expr()->field('coreVersion.current')->notEqual('7.31'));
    //);*/

    // @todo generate the data for this via a cron command into another collection for better querying.

    $cursor = $qb->getQuery()->execute();

    if ($cursor->count() < 1) {
      return array();
    }

    $sites = array();
    foreach ($cursor as $result) {
      $modules = array();
      foreach ($result->getModules() as $module) {
        if (!isset($module['latestVersion']) || $module['version'] == $module['latestVersion']) {
          continue;
        }
        $modules[] = $module;
      }
      $sites[] = array(
        'id' => $result->getId(),
        'url' => $result->getUrl(),
        'coreVersion' => $result->getCoreVersion(),
        'latestCoreVersion' => $result->getLatestCoreVersion(),
        'isSecurity' => $result->getIsSecurityCoreVersion(),
        'modules' => $modules,
      );
    }

    return $sites;
  }
}