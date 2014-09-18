<?php

namespace Deeson\SiteStatusBundle\Services;

use Deeson\SiteStatusBundle\Document\Module;
use Deeson\SiteStatusBundle\Exception\StatusRequestException;

class StatusRequestService extends BaseRequestService {

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
   * @var \Deeson\SiteStatusBundle\Document\Site $site
   */
  protected $site = NULL;

  /**
   * @param \Deeson\SiteStatusBundle\Document\Site $site
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
    return $this->site->getUrl() . '/admin/reports/system_status/' . $this->site->getSystemStatusToken();
  }

  /**
   * Processes the data that has come back from the request.
   *
   * @param $requestData
   *   Data that has come back from the request.
   */
  protected function processRequestData($requestData) {
    //printf('<pre>req: %s</pre>', print_r($requestData, true));
    $requestDataObject = json_decode($requestData);
    //printf('<pre>req obj: %s</pre>', print_r($requestDataObject, true));
    //die();

    if (is_string($requestDataObject->system_status) && $requestDataObject->system_status == 'encrypted') {
      $systemStatusData = $this->decrypt($requestDataObject->data, $this->site->getSystemStatusEncryptToken());
      $systemStatusDataObject = json_decode($systemStatusData);
    }
    else {
      // This request isn't encrypted so don't do anything with it but generate an alert?
      //throw new StatusRequestException('Request is not encrypted!');
      $systemStatusDataObject = $requestDataObject;
    }
    //printf('<pre>%s</pre>', print_r($systemStatusDataObject, true));
    //die();

    // Get the core version from the site.
    if (isset($systemStatusDataObject->system_status->core->drupal)) {
      $this->coreVersion = $systemStatusDataObject->system_status->core->drupal->version;
    }
    else {
      // No core data available - probably on pressflow!
      if (isset($requestDataObject->drupal_version)) {
        $coreVersion = $requestDataObject->drupal_version;
      }
      else {
        foreach ($systemStatusDataObject->system_status->contrib as $module) {
          $coreVersion = Module::getMajorVersion((string) $module->version);
          break;
        }
      }
      $this->coreVersion = $coreVersion . '.x';
    }

    //$this->coreVersion = isset($systemStatusDataObject->system_status->core->drupal) ? $systemStatusDataObject->system_status->core->drupal->version : '0';
    $this->moduleData = json_decode(json_encode($systemStatusDataObject->system_status->contrib), TRUE);
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

}