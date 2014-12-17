<?php

namespace Deeson\WardenBundle\Services;

use Buzz\Browser;
use Symfony\Bridge\Monolog\Logger;

abstract class BaseRequestService {

  /**
   * @var Browser
   */
  protected $buzz;

  /**
   * The connection timeout in seconds.
   *
   * @var int
   */
  protected $connectionTimeout = 20;

  /**
   * The array of headers to be used when making a curl connection.
   *
   * @var array
   */
  protected $connectionHeaders = array();

  /**
   * @var int
   */
  protected $requestTime = 0;

  /**
   * @var Logger
   */
  protected $logger;

  /**
   * Constructor
   *
   * @param Browser $buzz
   */
  public function __construct(Browser $buzz, Logger $logger) {
    $this->buzz = $buzz;
    $this->logger = $logger;
  }

  /**
   * Set the connection timeout.
   *
   * @param int $timeout
   */
  public function setConnectionTimeout($timeout) {
    $this->connectionTimeout = $timeout;
  }

  /**
   * Get the connection request time.
   *
   * @return int
   */
  public function getRequestTime() {
    return $this->requestTime;
  }

  /**
   * @param array $connectionHeaders
   */
  public function setConnectionHeaders(array $connectionHeaders) {
    $this->connectionHeaders = $connectionHeaders;
  }

  /**
   * Processes the request on a URL.
   *
   * This gets the URL from the method: getRequestUrl() and makes a call to
   * method: processRequestData() to process the request data.
   *
   * @TODO Consider changing the name of this function, its getting latest data
   * from a remote endpoint rather than processing a request.
   *
   * @see getRequestUrl()
   * @see processRequestData()
   */
  abstract public function processRequest();

  /**
   * Processes the data that has come back from the request.
   *
   * @param $requestData
   *   Data that has come back from the request.
   */
  abstract public function processRequestData($requestData);

  /**
   * @return mixed
   */
  abstract protected function getRequestUrl();

  /**
   * Set the connection timeout on the buzz client.
   *
   * @param int $timeout
   */
  protected function setClientTimeout($timeout) {
    $this->buzz->getClient()->setTimeout($timeout);
  }

  /**
   * Get the microtime.
   *
   * @return float
   */
  protected function getMicrotimeFloat() {
    list($usec, $sec) = explode(' ', microtime());
    return ((float)$usec + (float)$sec);
  }

}