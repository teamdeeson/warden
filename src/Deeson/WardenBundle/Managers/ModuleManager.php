<?php

namespace Deeson\WardenBundle\Managers;

use Deeson\WardenBundle\Document\ModuleDocument;
use Deeson\WardenBundle\Exception\DocumentNotFoundException;

class ModuleManager extends BaseManager {

  /**
   * Returns a boolean of whether a module with this name already exists.
   *
   * @param string $name
   *
   * @return bool
   */
  public function nameExists($name) {
    return $this->getRepository()->findBy(array('projectName' => $name));
  }

  /**
   * @return string
   *   The type of this manager.
   */
  public function getType() {
    return 'ModuleDocument';
  }

  /**
   * Create a new empty type of the object.
   *
   * @return Site
   */
  public function makeNewItem() {
    return new ModuleDocument();
  }

  /**
   * Find module by name.
   *
   * @param $name
   *
   * @return object
   * @throws DocumentNotFoundException
   */
  public function findByProjectName($name) {
    try {
      return $this->getDocumentBy(array('projectName' => $name));
    } catch (DocumentNotFoundException $e) {
      throw new DocumentNotFoundException($e->getMessage());
    }
  }

  /**
   * Returns a list of sites for the specific version.
   *
   * @param $version
   *
   * @return array
   * @throws \Doctrine\ODM\MongoDB\MongoDBException
   */
  public function getAllByVersion($version) {
    /** @var \Doctrine\ODM\MongoDB\Query\Builder $qb */
    $qb = $this->createQueryBuilder();
    $qb->field('latestVersion.' . $version)->exists(TRUE);

    $cursor = $qb->getQuery()->execute();

    $results = array();
    foreach ($cursor as $result) {
      $results[] = $result;
    }
    return $results;
  }
}