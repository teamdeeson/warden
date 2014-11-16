<?php

namespace Deeson\WardenBundle\Services;

use Deeson\WardenBundle\Document\ModuleDocument;
use Deeson\WardenBundle\Exception\WardenRequestException;

class WardenRequestService extends BaseRequestService {

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
   * @var \Deeson\WardenBundle\Document\SiteDocument $site
   */
  protected $site = NULL;

  /**
   * List of any additional errors that have come through from the site.
   *
   * @var array
   */
  protected $additionalIssues = array();

  /**
   * @param \Deeson\WardenBundle\Document\SiteDocument $site
   */
  public function setSite($site) {
    $this->site = $site;
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
   * Get the site status URL.
   *
   * @return mixed
   */
  protected function getRequestUrl() {
    return $this->site->getUrl() . '/admin/reports/warden/' . $this->site->getWardenToken();
  }

  /**
   * @return array
   */
  public function getAdditionalIssues() {
    return $this->additionalIssues;
  }

  /**
   * Processes the data that has come back from the request.
   *
   * @param $requestData
   *   Data that has come back from the request.
   */
  protected function processRequestData($requestData) {
    $requestDataObject = json_decode($requestData);

    // @todo add logging of response to a file.
    if (!isset($requestDataObject->warden)) {
      throw new WardenRequestException("Invalid return response - possibly access denied");
    }

    if (is_string($requestDataObject->warden) && $requestDataObject->warden == 'encrypted') {
      $wardenData = $this->decrypt($requestDataObject->data, $this->site->getWardenEncryptToken());
      $wardenDataObject = json_decode($wardenData);
    }
    else {
      // This request isn't encrypted so don't do anything with it but generate an alert?
      //throw new SiteStatusRequestException('Request is not encrypted!');
      $wardenDataObject = $requestDataObject;
    }

    // Get the core version from the site.
    if (isset($wardenDataObject->warden->core->drupal)) {
      $this->coreVersion = $wardenDataObject->warden->core->drupal->version;
    }
    else {
      // No core data available - probably on pressflow!
      if (isset($requestDataObject->drupal_version)) {
        $coreVersion = $requestDataObject->drupal_version;
      }
      else {
        foreach ($wardenDataObject->warden->contrib as $module) {
          $coreVersion = ModuleDocument::getMajorVersion((string) $module->version);
          break;
        }
      }
      $this->coreVersion = $coreVersion . '.x';
    }

    //$this->coreVersion = isset($wardenDataObject->warden->core->drupal) ? $wardenDataObject->warden->core->drupal->version : '0';
    $this->moduleData = json_decode(json_encode($wardenDataObject->warden->contrib), TRUE);
  }

  /**
   * Decrypt an encrypted message from the warden module on the site.
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

}