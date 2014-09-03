<?php

namespace Deeson\SiteStatusBundle\Managers;

use Deeson\SiteStatusBundle\Document\BaseDocument;
use Deeson\SiteStatusBundle\Exception\EntityMethodNotFoundException;

abstract class BaseManager {

  /**
   * @var \Doctrine\Bundle\MongoDBBundle\ManagerRegistry
   */
  protected $doctrine;

  /**
   * @var string
   */
  protected $type;

  public function __construct($doctrine) {
    $this->doctrine = $doctrine;
  }

  /**
   * @param $id
   *
   * @return object
   * @throws EntityNotFoundException
   */
  public function getEntityById($id) {
    $result = $this->getRepository()->find($id);
    if (empty($result)) {
      throw new EntityNotFoundException("No {$this->getType()} with id $id");
    }
    return $result;
  }

  /**
   * @return array
   * @throws EntityNotFoundException
   */
  public function getAllEntities() {
    $result = $this->getRepository()->findAll();
    if (empty($result)) {
      throw new EntityNotFoundException("No entities found for {$this->getType()}");
    }
    return $result;
  }

  public function getEntitiesBy(array $criteria, array $orderBy = null, $limit = null, $offset = null) {
    $result = $this->getRepository()->findBy($criteria, $orderBy, $limit, $offset);
    if (empty($result)) {
      throw new EntityNotFoundException("No entities found for {$this->getType()}");
    }
    return $result;
  }

  public function getEntityBy(array $criteria) {
    $result = $this->getRepository()->findOneBy($criteria);
    if (empty($result)) {
      throw new EntityNotFoundException("No entities found for {$this->getType()}");
    }
    return $result;
  }

  public function updateEntity($id, array $data) {
    $entity = $this->getEntityById($id);

    foreach ($data as $key => $value) {
      $method = 'set' . ucfirst($key);
      if (!method_exists($entity, $method)) {
        throw new EntityMethodNotFoundException("$method is not a valid method for {$this->getType()}");
        continue;
      }
      $entity->$method($value);
    }

    $this->doctrine->getManager()->flush();
  }

  public function saveEntity($entity) {
    $this->doctrine->getManager()->persist($entity);
    $this->doctrine->getManager()->flush();
  }

  public function deleteEntity($id) {
    $entity = $this->getEntityById($id);

    $this->doctrine->getManager()->remove($entity);
    $this->doctrine->getManager()->flush();
  }

  /**
   * Create a new empty type of the object.
   *
   * @return BaseDocument
   */
  public abstract function makeNewItem();

  /**
   * @return string
   *   The type of this manager.
   *   e.g. 'Site'
   */
  public abstract function getType();

  /**
   * Get the Doctrine ObjectRepository for the respective collection.
   *
   * @return \Doctrine\Common\Persistence\ObjectRepository
   */
  protected function getRepository() {
    return $this->doctrine->getRepository($this->getRepositoryName());
  }

  /**
   * The Mongodb repository name.
   *
   * @return string
   */
  protected function getRepositoryName() {
    return 'DeesonSiteStatusBundle:' . $this->getType();
  }

}