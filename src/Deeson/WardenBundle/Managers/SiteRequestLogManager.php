<?php

namespace Deeson\WardenBundle\Managers;

use ArturDoruch\PaginatorBundle\Paginator;
use Deeson\WardenBundle\Document\SiteDocument;
use Deeson\WardenBundle\Document\SiteRequestLogDocument;
use MongoDB\BSON\ObjectId;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SiteRequestLogManager extends BaseManager {

  /**
   * @var SiteManager
   */
  protected $siteManager;

  /**
   * @var ContainerInterface
   */
  protected $container;

  /**
   * @var Pagination $paginator
   */
  protected $paginator;

  public function __construct($doctrine, Logger $logger, Paginator $paginator) {
    parent::__construct($doctrine, $logger);
    $this->paginator = $paginator;
  }

  /**
   * @return string
   *   The type of this manager.
   *   e.g. 'SiteRequestLogDocument'
   */
  public function getType() {
    return 'SiteRequestLogDocument';
  }

  /**
   * Create a new empty type of the object.
   *
   * @return SiteRequestLogDocument
   */
  public function makeNewItem() {
    return new SiteRequestLogDocument();
  }

  /**
   * Adds a successful log message.
   *
   * @param \Deeson\WardenBundle\Document\SiteDocument $site
   * @param $message
   * @param $response
   */
  public function addSuccessfulLog(SiteDocument $site, $message, $response = null) {
    $this->addLog($site, true, $message, $response);
  }

  /**
   * Adds a failed log message.
   *
   * @param \Deeson\WardenBundle\Document\SiteDocument $site
   * @param $message
   * @param $response
   */
  public function addFailedLog(SiteDocument $site, $message, $response = null) {
    $this->addLog($site, false, $message, $response);
  }

  /**
   * Get the list of request log records.
   *
   * @param string $siteId
   * @param int $page
   * @param int $limit
   *
   * @return array
   */
  public function getRequestLogs($siteId, $page = 1, $limit = 20) {
    /** @var \Doctrine\ODM\MongoDB\Query\Builder $qb */
    $qb = $this->createQueryBuilder();
    $qb->select(array('id', 'timestamp', 'status', 'message', 'response'));
    $qb->field('siteId')->equals(new ObjectId($siteId));
    $qb->sort('timestamp', 'DESC');

    return $this->paginator->paginate($qb, $page, $limit);
  }

  /**
   * Add a log entry to the site request log.
   *
   * @param SiteDocument $site
   * @param bool $status
   * @param string $message
   * @param $response
   *
   * @return bool
   *   True if the site has been added otherwise false.
   */
  protected function addLog(SiteDocument $site, $status, $message, $response = null) {
    /** @var SiteRequestLogDocument $siteRequestLog */
    $siteRequestLog = $this->makeNewItem();
    $siteRequestLog->setSiteId($site->getId());
    $siteRequestLog->setTimestamp(time());
    $siteRequestLog->setStatus($status);
    $siteRequestLog->setMessage($message);
    $siteRequestLog->setResponse($response);

    $this->saveDocument($siteRequestLog);

    return TRUE;
  }

}
