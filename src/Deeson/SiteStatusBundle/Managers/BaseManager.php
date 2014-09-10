<?php

namespace Deeson\SiteStatusBundle\Managers;

use Deeson\SiteStatusBundle\Document\BaseDocument;
use Deeson\SiteStatusBundle\Exception\DocumentMethodNotFoundException;
use Deeson\SiteStatusBundle\Exception\DocumentNotFoundException;

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
   * Get a specific Mongodb document by id.
   *
   * @param $id
   *   The Mongodb Object Id.
   *
   * @return BaseDocument
   * @throws DocumentNotFoundException
   */
  public function getDocumentById($id) {
    $result = $this->getRepository()->find($id);
    if (empty($result)) {
      throw new DocumentNotFoundException("No {$this->getType()} with id $id");
    }
    return $result;
  }

  /**
   * Get all the Mongodb documents.
   *
   * @return array
   *   Mongodb document objects.
   * @throws DocumentNotFoundException
   */
  public function getAllDocuments() {
    $result = $this->getRepository()->findAll();
    if (empty($result)) {
      return array();
      //throw new DocumentNotFoundException("No documents found for {$this->getType()}");
    }
    return $result;
  }

  /**
   * Get all Mongodb documents by a criteria.
   *
   * @param array $criteria
   * @param array $orderBy
   * @param null $limit
   * @param null $offset
   *
   * @return array
   *   Mongodb document objects.
   * @throws \Deeson\SiteStatusBundle\Exception\DocumentNotFoundException
   */
  public function getDocumentsBy(array $criteria, array $orderBy = null, $limit = null, $offset = null) {
    $result = $this->getRepository()->findBy($criteria, $orderBy, $limit, $offset);
    if (empty($result)) {
      throw new DocumentNotFoundException("No documents found for {$this->getType()}");
    }
    return $result;
  }

  /**
   * Get one document based on a criteria.
   *
   * @param array $criteria
   *
   * @return Mongodb document object.
   * @throws \Deeson\SiteStatusBundle\Exception\DocumentNotFoundException
   */
  public function getDocumentBy(array $criteria) {
    $result = $this->getRepository()->findOneBy($criteria);
    if (empty($result)) {
      throw new DocumentNotFoundException("No documents found for {$this->getType()}");
    }
    return $result;
  }

  /**
   * Updates the Mongodb document based upon the Mongodb Id.
   *
   * @param int $id
   *   The Mongodb document Object Id
   * @param array $data
   *   Array of data to update on the object. The array key is the column and
   *   the array value is the value to be set.
   *
   * @throws \Deeson\SiteStatusBundle\Exception\DocumentMethodNotFoundException
   */
  /*public function updateDocumentById($id, array $data) {
    $entity = $this->getDocumentById($id);

    foreach ($data as $key => $value) {
      $method = 'set' . ucfirst($key);
      if (!method_exists($entity, $method)) {
        throw new DocumentMethodNotFoundException("$method is not a valid method for {$this->getType()}");
      }
      $entity->$method($value);
    }

    $this->doctrine->getManager()->flush();
  }*/

  /**
   * Update the Mongodb document.
   */
  public function updateDocument() {
    $this->doctrine->getManager()->flush();
  }

  /**
   * Save the Mongodb document.
   *
   * @param $document
   *   Mongodb document object.
   */
  public function saveDocument($document) {
    $this->doctrine->getManager()->persist($document);
    $this->doctrine->getManager()->flush();
  }

  /**
   * Delete the Mongodb document.
   *
   * @param int $id
   *   The Mongodb Object Id.
   */
  public function deleteDocument($id) {
    $document = $this->getDocumentById($id);

    $this->doctrine->getManager()->remove($document);
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