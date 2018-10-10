<?php

namespace Deeson\WardenBundle\Client;

use Buzz\Browser;
use Buzz\Client\Curl;
use Buzz\Exception\ClientException;
use Symfony\Component\HttpFoundation\Response;

class BuzzRequestHandler implements RequestHandlerInterface {

  /**
   * @var Browser
   */
  protected $buzzBrowser;

  /**
   * @var array
   */
  protected $headers = [];

  public function __construct() {
    $client = new Curl();
    $this->buzzBrowser = new Browser($client);
  }

  /**
   * {@inheritDoc}
   */
  public function setTimeout($timeout) {
    $this->buzzBrowser->getClient()->setTimeout($timeout);
  }

  /**
   * {@inheritDoc}
   */
  public function setVerifySslCert($verify) {
    $this->buzzBrowser->getClient()->setVerifyPeer($verify);
    $this->buzzBrowser->getClient()->setVerifyHost($verify);
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
      /** @var \Buzz\Message\Response $response */
      $response = $this->buzzBrowser->get($url, $this->headers);
      return new Response($response->getContent(), $response->getStatusCode(), $response->getHeaders());
    } catch (ClientException $e) {
      throw new RequestHandlerException($e->getMessage());
    }
  }

  /**
   * {@inheritDoc}
   */
  public function post($url, $content = '') {
    try {
      /** @var \Buzz\Message\Response $response */
      $response = $this->buzzBrowser->post($url, $this->headers, $content);
      return new Response($response->getContent(), $response->getStatusCode(), $response->getHeaders());
    } catch (ClientException $e) {
      throw new RequestHandlerException($e->getMessage());
    }
  }
}
