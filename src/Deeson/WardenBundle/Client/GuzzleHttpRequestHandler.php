<?php

namespace Deeson\WardenBundle\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
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
  protected $verifyPeer = true;


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
  public function setVerifyPeer($verifyPeer) {
    $this->verifyPeer = $verifyPeer;
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
    } catch (RequestException $e) {
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
      //print __METHOD__;
      //printf('<pre>%s</pre>', print_r($response, true));
      return new Response($response->getBody(), $response->getStatusCode(), $response->getHeaders());
    } catch (RequestException $e) {
      printf('<pre>%s</pre>', print_r($this->getRequestOptions(), true));
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
    $options['verify'] = $this->verifyPeer;
    if ($this->timeout > 0) {
      $options['timeout'] = $this->timeout;
    }
    return $options /*+ $this->headers*/;
  }
}
