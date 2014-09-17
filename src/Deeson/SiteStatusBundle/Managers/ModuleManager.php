<?php

namespace Deeson\SiteStatusBundle\Managers;

use Deeson\SiteStatusBundle\Document\Module;
use Deeson\SiteStatusBundle\Exception\DocumentNotFoundException;

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
    return 'Module';
  }

  /**
   * Create a new empty type of the object.
   *
   * @return Site
   */
  public function makeNewItem() {
    return new Module();
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

  public function getAllByVersion($version) {
    $qb = $this->createIndexQuery();
    $qb->field('latestVersion.' . $version)->exists(TRUE);

    $cursor = $qb->getQuery()->execute();

    $results = array();
    foreach ($cursor as $result) {
      $results[] = $result;
    }
    return $results;
  }
}