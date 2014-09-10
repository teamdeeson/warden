<?php

namespace Deeson\SiteStatusBundle\Services;


class DrupalUpdateRequestService extends BaseRequestService {

  /**
   * The name of the module.
   *
   * @var string
   */
  protected $moduleRequestName;

  /**
   * The version of the module.
   *
   * @var string
   */
  protected $moduleRequestVersion;

  protected $moduleName;

  protected $moduleLatestRelease;

  /**
   * @param string $moduleVersion
   */
  public function setModuleRequestVersion($moduleVersion) {
    $this->moduleRequestVersion = $moduleVersion . '.x';
  }

  /**
   * @param string $name
   */
  public function setModuleRequestName($name) {
    $this->moduleRequestName = $name;
  }

  /**
   * @return mixed
   */
  public function getModuleLatestRelease() {
    return $this->moduleLatestRelease;
  }

  /**
   * @return mixed
   */
  public function getModuleName() {
    return $this->moduleName;
  }


  /**
   * Processes the data that has come back from the request.
   *
   * @param $requestData
   *   Data that has come back from the request.
   * @throws \Exception
   */
  protected function processRequestData($requestData) {
    //printf('<pre>%s</pre>', print_r($requestData, true));
    $requestXmlObject = simplexml_load_string($requestData);

    if (!isset($requestXmlObject->title)) {
      throw new \Exception('Error getting date for module: ' . $this->moduleRequestName);
      //throw new DrupalUpdateException();
    }

    //print_r($requestXmlObject);
    //$title = $requestXmlObject->xpath('/project');
    //$title = (string) $requestXmlObject->title;
    //print_r($title);
    //$release = $requestXmlObject->xpath('/project/releases/release[1]');
    $release = $requestXmlObject->releases->release[0];
    //print_r($release);

    $this->moduleName = (string) $requestXmlObject->title;
    $this->moduleLatestRelease = $release;
  }

  /**
   * @return mixed
   */
  protected function getRequestUrl() {
    return 'http://updates.drupal.org/release-history/' . $this->moduleRequestName . '/' . $this->moduleRequestVersion;
  }


}