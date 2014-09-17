<?php

namespace Deeson\SiteStatusBundle\Services;

abstract class BaseRequestService {

  /**
   * @var \Buzz\Browser
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
   * Constructor
   */
  public function __construct($buzz) {
    $this->buzz = $buzz;
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
   * Processes the request on a URL.
   *
   * This gets the URL from the method: getRequestUrl() and makes a call to
   * method: processRequestData() to process the request data.
   *
   * @see getRequestUrl()
   * @see processRequestData()
   */
  public function processRequest() {
    $this->setClientTimeout($this->connectionTimeout);

    $startTime = $this->getMicrotimeFloat();

    $request = $this->buzz->get($this->getRequestUrl(), $this->connectionHeaders);
    // @todo check request header, if not 200 throw exception.
    /*$headers = $request->getHeaders();
    if (trim($headers[0]) !== 'HTTP/1.0 200 OK') {
      print 'invalid response'."\n";
      print_r($headers);
      //return;
    }*/
    $requestData = $request->getContent();

    $endTime = $this->getMicrotimeFloat();
    $this->requestTime = $endTime - $startTime;

    $this->processRequestData($requestData);
  }

  /**
   * Processes the data that has come back from the request.
   *
   * @param $requestData
   *   Data that has come back from the request.
   */
  abstract protected function processRequestData($requestData);

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