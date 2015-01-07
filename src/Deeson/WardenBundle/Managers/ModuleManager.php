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
   * @return ModuleDocument
   */
  public function getModule($name) {
    $result = $this->getRepository()->findBy(array('projectName' => $name));
    $moduleExistsCount = $result->count();

    if ($moduleExistsCount == 0) {
      return NULL;
    }

    return $result->getNext();
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
   * @return ModuleDocument
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

  /**
   * Add a list of modules to Warden.
   *
   * @param array $moduleData
   */
  public function addModules(array $moduleData) {
    foreach ($moduleData as $name => $version) {
      $majorVersion = ModuleDocument::getMajorVersion($version['version']);

      if (!is_string($majorVersion)) {
        $this->logger->addWarning("Badly formed major version for module $name");
        continue;
      }

      $module = $this->getModule($name);

      if (empty($module)) {
        $module = $this->makeNewItem();
      }

      if (!array_key_exists($majorVersion, $module->getLatestVersion())) {
        $this->logger->addInfo('ModuleManager: Going to add details about module: ' . $name . ' version: ' . $version['version']);
        $module->setProjectName($name);
        $module->setLatestVersion($majorVersion);
        $this->saveDocument($module);
      }
    }
  }
}