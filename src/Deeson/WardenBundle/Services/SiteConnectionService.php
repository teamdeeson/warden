<?php

namespace Deeson\WardenBundle\Services;

use Deeson\WardenBundle\Client\HttpRequestHandlerException;
use Deeson\WardenBundle\Client\HttpRequestHandlerInterface;
use Deeson\WardenBundle\Document\SiteDocument;
use Deeson\WardenBundle\Exception\WardenRequestException;
use Deeson\WardenBundle\Managers\SiteRequestLogManager;
use Symfony\Bridge\Monolog\Logger;


class SiteConnectionService {

  /**
   * @var HttpRequestHandlerInterface
   */
  protected $client;

  /**
   * @var SSLEncryptionService
   */
  protected $sslEncryptionService;

  /**
   * @var Logger
   */
  protected $logger;

  /**
   * @var SiteRequestLogManager
   */
  protected $siteRequestLogManager;

  /**
   * @var string
   */
  protected $environment;

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
   *
   * @param HttpRequestHandlerInterface $client
   * @param SSLEncryptionService $sslEncryptionService
   * @param Logger $logger
   * @param SiteRequestLogManager $siteRequestLogManager
   * @param string $environment
   */
  public function __construct(HttpRequestHandlerInterface $client, SSLEncryptionService $sslEncryptionService, Logger $logger, SiteRequestLogManager $siteRequestLogManager, $environment) {
    $this->client = $client;
    $this->sslEncryptionService = $sslEncryptionService;
    $this->logger = $logger;
    $this->siteRequestLogManager = $siteRequestLogManager;
    $this->environment = $environment;
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
   * @param string $url
   *   The URL to POST to
   * @param SiteDocument $site
   *   The site being posted to
   * @param array $params
   *   An array of keys and values to be posted
   *
   * @throws WardenRequestException
   *   If any error occurs
   */
  public function post($url, SiteDocument $site, array $params = array()) {
    try {
      if ($site->getAuthUser() && $site->getAuthPass()) {
        $headers = array(sprintf('Authorization: Basic %s', base64_encode($site->getAuthUser() . ':' . $site->getAuthPass())));
        $this->connectionHeaders = $headers;
      }

      $params['token'] = $this->sslEncryptionService->generateRequestToken();

      /** @var \Symfony\Component\HttpFoundation\Response $response */
      $this->client->setTimeout($this->connectionTimeout);

      if ($this->environment === 'dev') {
        // Don't verify SSL certificate.
        $this->client->setVerifySslCert(FALSE);
      }

      $this->client->setHeaders($this->connectionHeaders);
      $response = $this->client->post($url, $params);

      if (!$response->isSuccessful()) {
        $this->siteRequestLogManager->addFailedLog($site, 'Unable to request data from ' . $url, $response);
        $this->logger->addError("Unable to request data from {$url}\nStatus code: " . $response->getStatusCode() . "\nHeaders: " . print_r($response->headers->__toString(), TRUE));
        throw new WardenRequestException("Unable to request data from {$url}. Check log for details.");
      }

      $this->siteRequestLogManager->addSuccessfulLog($site, 'Successfully sent request to the site.', $response);
    }
    catch (HttpRequestHandlerException $clientException) {
      $this->siteRequestLogManager->addFailedLog($site, 'Failed to connect to the site.', $clientException->getMessage());
      throw new WardenRequestException($clientException->getMessage());
    }
  }

}
