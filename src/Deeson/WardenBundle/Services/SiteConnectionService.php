<?php

namespace Deeson\WardenBundle\Services;

use Buzz\Browser;
use Buzz\Exception\ClientException;
use Deeson\WardenBundle\Document\SiteDocument;
use Deeson\WardenBundle\Exception\WardenRequestException;
use Deeson\WardenBundle\Managers\SiteManager;
use Symfony\Bridge\Monolog\Logger;


class SiteConnectionService {

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
   * @var SSLEncryptionService
   */
  protected $sslEncryptionService;

  /**
   * @var Logger
   */
  protected $logger;

  /**
   * @var SiteManager
   */
  protected $siteManager;

  /**
   * Constructor
   *
   * @param Browser $buzz
   * @param SiteManager $siteManager
   * @param SSLEncryptionService $sslEncryptionService
   * @param Logger $logger
   */
  public function __construct(Browser $buzz, SiteManager $siteManager, SSLEncryptionService $sslEncryptionService, Logger $logger) {
    $this->buzz = $buzz;
    $this->sslEncryptionService = $sslEncryptionService;
    $this->logger = $logger;
    $this->siteManager = $siteManager;
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
   * Set the connection timeout on the buzz client.
   *
   * @param int $timeout
   */
  protected function setClientTimeout($timeout) {
    $this->buzz->getClient()->setTimeout($timeout);
  }

  /**
   * @param string $url
   *   The URL to POST to
   * @param SiteDocument $site
   *   The site being posted to
   * @param array $params
   *   An array of keys and values to be posted
   *
   * @return mixed
   *   The content of the response
   *
   * @throws WardenRequestException
   *   If any error occurs
   */
  public function post($url, SiteDocument $site, array $params = array()) {
    try {
      $this->setClientTimeout($this->connectionTimeout);
      // Don't verify SSL certificate.
      // @TODO make this optional
      $this->buzz->getClient()->setVerifyPeer(FALSE);

      if ($site->getAuthUser() && $site->getAuthPass()) {
        $headers = array(sprintf('Authorization: Basic %s', base64_encode($site->getAuthUser() . ':' . $site->getAuthPass())));
        $this->setConnectionHeaders($headers);
      }

      $params['token'] = $this->sslEncryptionService->generateRequestToken();
      $content = http_build_query($params);

      /** @var \Buzz\Message\Response $response */
      $response = $this->buzz->post($url, $this->connectionHeaders, $content);

      if (!$response->isSuccessful()) {
        $this->logger->addError("Unable to request data from {$url}\nStatus code: " . $response->getStatusCode() . "\nHeaders: " . print_r($response->getHeaders(), TRUE));
        throw new WardenRequestException("Unable to request data from {$url}. Check log for details.");
      }

      $site->setLastSuccessfulRequest();
      $this->siteManager->updateDocument();
    }
    catch (ClientException $clientException) {
      throw new WardenRequestException($clientException->getMessage());
    }
  }

}
