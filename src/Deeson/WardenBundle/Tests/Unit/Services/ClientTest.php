<?php

namespace Deeson\WardenBundle\Test\Unit\Services;

use Deeson\WardenBundle\Client\GuzzleHttpRequestHandler;

class ClientTest extends \PHPUnit_Framework_TestCase {

  /**
   * @var \Deeson\WardenBundle\Client\HttpRequestHandlerInterface
   */
  protected $client;

  public function setUp() {
    $this->client = new GuzzleHttpRequestHandler();
  }

  public function testGet() {
    /** @var \Symfony\Component\HttpFoundation\Response $response */
    $response = $this->client->get('https://www.google.com');

    $this->equalTo(200, $response->getStatusCode());
  }

  public function testPost() {
    $response = $this->client->post('https://warden.deeson.net', 'TOK1234');

    $this->equalTo(200, $response->getStatusCode());
    $this->equalTo('123', $response->getContent());
  }

  public function testPostInsecure() {
    $this->client->setVerifyPeer(false);
    $response = $this->client->post('https://admin.localhost', 'TOK1234');

    $this->equalTo(200, $response->getStatusCode());
    $this->equalTo('123', $response->getContent());
  }

  public function testPostTimeout() {
    $this->client->setVerifyPeer(false);
    $this->client->setTimeout(10);
    $response = $this->client->post('http://localhost/test', 'TOK1234');
    printf('<pre>%s</pre>', print_r($response, true));

    $this->equalTo(200, $response->getStatusCode());
    //$this->equalTo('123', $response->getContent());
  }
}
