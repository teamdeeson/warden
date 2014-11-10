<?php

namespace Deeson\WardenBundle\Managers;

use Deeson\WardenBundle\Document\BaseDocument;
use Deeson\WardenBundle\Exception\DocumentMethodNotFoundException;
use Deeson\WardenBundle\Exception\DocumentNotFoundException;

abstract class BaseManager {

  /**
   * @var \Doctrine\Bundle\MongoDBBundle\ManagerRegistry
   */
  protected $doctrine;

  /**
   * @var \Doctrine\ODM\MongoDB\DocumentManager
   */
  protected $doctrineManager;

  /**
   * @var string
   */
  protected $type;

  public function __construct($doctrine) {
    $this->doctrine = $doctrine;
    $this->doctrineManager = $this->doctrine->getManager();
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
   * @throws \Deeson\WardenBundle\Exception\DocumentNotFoundException
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
   * @throws \Deeson\WardenBundle\Exception\DocumentNotFoundException
   */
  public function getDocumentBy(array $criteria) {
    $result = $this->getRepository()->findOneBy($criteria);
    if (empty($result)) {
      throw new DocumentNotFoundException("No documents found for {$this->getType()}");
    }
    return $result;
  }

  /**
   * Update the Mongodb document.
   */
  public function updateDocument() {
    $this->doctrineManager->flush();
  }

  /**
   * Save the Mongodb document.
   *
   * @param $document
   *   Mongodb document object.
   */
  public function saveDocument($document) {
    $this->doctrineManager->persist($document);
    $this->doctrineManager->flush();
  }

  /**
   * Delete the Mongodb document.
   *
   * @param int $id
   *   The Mongodb Object Id.
   */
  public function deleteDocument($id) {
    $document = $this->getDocumentById($id);

    $this->doctrineManager->remove($document);
    $this->doctrineManager->flush();
  }

  /**
   * Deletes all contents of the collection.
   *
   * @throws \Doctrine\ODM\MongoDB\MongoDBException
   */
  public function deleteAll() {
    $this->createQueryBuilder()->remove()->getQuery()->execute();
  }

  /**
   * Creates a Doctrine Query Builder.
   *
   * @return \Doctrine\ODM\MongoDB\Query\Builder
   */
  public function createQueryBuilder() {
    $documentName = $this->getRepositoryName();
    return $this->doctrineManager->createQueryBuilder($documentName);
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
    return 'DeesonWardenBundle:' . $this->getType();
  }

}