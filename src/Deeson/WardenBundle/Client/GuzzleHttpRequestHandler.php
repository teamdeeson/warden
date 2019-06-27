<?php

namespace Deeson\WardenBundle\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpFoundation\Response;

class GuzzleHttpRequestHandler implements HttpRequestHandlerInterface {

  /**
   * @var Client
   */
  protected $client;

  /**
   * @var array
   */
  protected $headers = [];

  /**
   * @var int
   */
  protected $timeout = 0;

  /**
   * @var bool
   */
  protected $verifySslCert = true;


  public function __construct() {
    $this->client = new Client();
  }

  /**
   * {@inheritDoc}
   */
  public function setTimeout($timeout) {
    $this->timeout = $timeout;
  }

  /**
   * {@inheritDoc}
   */
  public function setVerifySslCert($verify) {
    $this->verifySslCert = $verify;
  }

  /**
   * {@inheritDoc}
   */
  public function setHeaders($headers) {
    $this->headers = $headers;
  }

  /**
   * {@inheritDoc}
   */
  public function get($url) {
    try {
      /** @var \Psr\Http\Message\ResponseInterface $response */
      $response = $this->client->request('GET', $url, $this->getRequestOptions());
      return new Response($response->getBody(), $response->getStatusCode(), $response->getHeaders());
    } catch (GuzzleException $e) {
      throw new HttpRequestHandlerException($e->getMessage());
    }
  }

  /**
   * {@inheritDoc}
   */
  public function post($url, $content = '') {
    try {
      /** @var \Psr\Http\Message\ResponseInterface $response */
      $response = $this->client->request('POST', $url, $this->getRequestOptions() + ['body' => $content]);
      return new Response($response->getBody(), $response->getStatusCode(), $response->getHeaders());
    } catch (GuzzleException $e) {
      throw new HttpRequestHandlerException($e->getMessage());
    }
  }

  /**
   * Builds the request options.
   *
   * @return array
   *   The array of options.
   */
  protected function getRequestOptions() {
    $options = [];
    $options['verify'] = $this->verifySslCert;
    if ($this->timeout > 0) {
      $options['timeout'] = $this->timeout;
    }
    return $options;
  }
}
