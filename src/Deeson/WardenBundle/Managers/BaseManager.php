<?php

namespace Deeson\WardenBundle\Managers;

use Deeson\WardenBundle\Document\BaseDocument;
use Deeson\WardenBundle\Exception\DocumentNotFoundException;
use Monolog\Logger;

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
   * @var Logger
   */
  protected $logger;

  /**
   * @var string
   */
  protected $type;

  public function __construct($doctrine, Logger $logger) {
    $this->doctrine = $doctrine;
    $this->doctrineManager = $this->doctrine->getManager();
    $this->logger = $logger;
  }

  /**
   * Get a specific Mongodb document by id.
   *
   * @param $id
   *   The Mongodb Object Id.
   *
   * @return Object
   * @throws DocumentNotFoundException
   */
  public function getDocumentById($id) {
    return $this->getRepository()->find($id);
  }

  /**
   * Get all the Mongodb documents.
   *
   * @return array
   *   Mongodb document objects.
   * @throws DocumentNotFoundException
   */
  public function getAllDocuments() {
    return $this->getRepository()->findAll();
  }

  /**
   * Get the count for all the documents.
   *
   * @return int
   *   The total document count.
   *
   * @throws \Doctrine\ODM\MongoDB\MongoDBException
   */
  public function getAllDocumentsCount() {
    return $this->createQueryBuilder()->getQuery()->execute()->count();
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
    return $this->getRepository()->findBy($criteria, $orderBy, $limit, $offset);
  }

  /**
   * Get one document based on a criteria.
   *
   * @param array $criteria
   *
   * @return Object.
   * @throws \Deeson\WardenBundle\Exception\DocumentNotFoundException
   */
  public function getDocumentBy(array $criteria) {
    return $this->getRepository()->findOneBy($criteria);
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
