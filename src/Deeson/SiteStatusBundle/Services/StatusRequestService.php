<?php

namespace Deeson\SiteStatusBundle\Services;

use Deeson\SiteStatusBundle\Exception\StatusRequestException;

class StatusRequestService {

  /**
   * @var \Buzz\Browser
   */
  protected $buzz;

  /**
   * The connection timeout in seconds.
   *
   * @var int
   */
  protected $connectionTimeout = 10;

  /**
   * The array of headers to be used when making a curl connection.
   *
   * @var array
   */
  protected $connectionHeaders = array();

  /**
   * Drupal core version.
   *
   * @var float
   */
  protected $coreVersion = 0;

  /**
   * List of contrib modules.
   *
   * @var array
   */
  protected $moduleData = array();

  /**
   * @var int
   */
  protected $requestTime = 0;

  /**
   * @var \Deeson\SiteStatusBundle\Document\Site $site
   */
  protected $site = NULL;

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
   * @param \Deeson\SiteStatusBundle\Document\Site $site
   */
  public function setSite($site) {
    $this->site = $site;
  }

  /**
   * Get the status data for the site.
   *
   * @return stdclass object.
   */
  public function requestSiteStatusData() {
    $dataRequest = $this->getRequestData();
    $systemStatusDataObject = $this->processSiteData($dataRequest);

    $this->coreVersion = $systemStatusDataObject->system_status->core->drupal->version;
    $this->moduleData = json_decode(json_encode($systemStatusDataObject->system_status->contrib), TRUE);
  }

  /**
   * Get the core version for the site.
   *
   * @return float
   */
  public function getCoreVersion() {
    return $this->coreVersion;
  }

  /**
   * Get the modules data for the site.
   *
   * @return array
   */
  public function getModuleData() {
    return $this->moduleData;
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
   * Get the site status URL.
   *
   * @param string $url
   * @param string $token
   *
   * @return string
   */
  protected function getSiteStatusUrl($url, $token) {
    return $url . '/admin/reports/system_status/' . $token;
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
   * @return string
   */
  protected function getRequestData() {
    $siteStatusUrl = $this->getSiteStatusUrl($this->site->getUrl(), $this->site->getSystemStatusToken());
    $this->setClientTimeout($this->connectionTimeout);

    $startTime = $this->getMicrotimeFloat();

    $request = $this->buzz->get($siteStatusUrl, $this->connectionHeaders);
    $requestData = $request->getContent();

    $endTime = $this->getMicrotimeFloat();
    $this->requestTime = $endTime - $startTime;

    return $requestData;
  }

  /**
   * Process the data returned from the request.
   *
   * @param string $requestData
   *
   * @return stdClass object
   *   The data from the request.
   */
  protected function processSiteData($requestData) {
    //printf('<pre>req: %s</pre>', print_r($data_request, true));
    $requestDataObject = json_decode($requestData);
    //printf('<pre>req obj: %s</pre>', print_r($data_request_object, true));

    if (is_string($requestDataObject->system_status) && $requestDataObject->system_status == 'encrypted') {
      $systemStatusData = $this->decrypt($requestDataObject->data, $this->site->getSystemStatusEncryptToken());
      $systemStatusDataObject = json_decode($systemStatusData);
    }
    else {
      // This request isn't encrypted so don't do anything with it but generate an alert?
      throw new StatusRequestException('Request is not encrypted!');
      $systemStatusDataObject = $requestDataObject->system_status;
    }

    return $systemStatusDataObject;
  }

  /**
   * Decrypt an encrypted message from the system_status module on the site.
   *
   * @param string $cipherTextBase64
   * @param string $encryptToken
   *
   * @return string
   */
  protected function decrypt($cipherTextBase64, $encryptToken) {
    $key = hash('SHA256', $encryptToken, TRUE);

    $ivSize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
    $cipherTextDec = base64_decode($cipherTextBase64);
    $ivDec = substr($cipherTextDec, 0, $ivSize);
    $cipherTextDec = substr($cipherTextDec, $ivSize);
    $plaintextDec = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $cipherTextDec, MCRYPT_MODE_CBC, $ivDec);

    return utf8_decode(trim($plaintextDec));
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