<?php

namespace Deeson\WardenBundle\Client;

interface RequestHandlerInterface {

  /**
   * Sets the HTTP timeout time in seconds.
   *
   * @param int $timeout
   *   timeout time in seconds
   *
   * @return mixed
   */
  public function setTimeout($timeout);

  /**
   * Sets whether to verify the SSL connection.
   *
   * @param bool $verify
   *
   * @return mixed
   */
  public function setVerifySslCert($verify);

  /**
   * Sets any request headers.
   *
   * @param array $headers
   */
  public function setHeaders($headers);

  /**
   * Perform a HTTP GET request.
   *
   * @param string $url
   *   The URL for the HTTP GET request.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *
   * @throws \Deeson\WardenBundle\Client\RequestHandlerException
   */
  public function get($url);

  /**
   * Perform a HTTP POST request.
   *
   * @param $url
   *   The URL for the HTTP POST request.
   * @param string $content
   *   The content for the HTTP POST request.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *
   * @throws \Deeson\WardenBundle\Client\RequestHandlerException
   */
  public function post($url, $content = '');

}
