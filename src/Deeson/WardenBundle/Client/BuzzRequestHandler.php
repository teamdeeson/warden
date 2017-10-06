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
  protected $client;

  /**
   * @var array
   */
  protected $headers = [];

  public function __construct() {
    $client = new Curl();
    $this->client = new Browser($client);
  }

  /**
   * {@inheritDoc}
   */
  public function setTimeout($timeout) {
    $this->client->getClient()->setTimeout($timeout);
  }

  /**
   * {@inheritDoc}
   */
  public function setVerifyPeer($verifyPeer) {
    $this->client->getClient()->setVerifyPeer($verifyPeer);
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
      $response = $this->client->get($url, $this->headers);
      return new Response($response->getContent(), $response->getStatusCode(), $response->getHeaders());
    } catch (ClientException $e) {
      throw new RequestHandlerException($e);
    }
  }

  /**
   * {@inheritDoc}
   */
  public function post($url, $content = '') {
    try {
      /** @var \Buzz\Message\Response $response */
      $response = $this->client->post($url, $this->headers, $content);
      return new Response($response->getContent(), $response->getStatusCode(), $response->getHeaders());
    } catch (ClientException $e) {
      throw new RequestHandlerException($e);
    }
  }
}
