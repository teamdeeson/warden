<?php

namespace Deeson\WardenBundle\Managers;

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

  public function __construct($doctrine, Logger $logger) {
    parent::__construct($doctrine, $logger);
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
   * @param $siteId
   *
   * @return array
   */
  public function getRequestLogs($siteId) {
    $siteRequestLogs = $this->getDocumentsBy(array('siteId' => new ObjectId($siteId)), array('timestamp' => 'desc'), 20);
    return $siteRequestLogs;
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
