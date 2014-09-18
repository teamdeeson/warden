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

  /**
   * @var string
   */
  protected $moduleName;

  /**
   * @var string
   */
  protected $moduleVersions;

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
  public function getModuleName() {
    return $this->moduleName;
  }

  /**
   * @return string
   */
  public function getModuleVersions() {
    return $this->moduleVersions;
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

    $recommendedMajorVersion = (string) $requestXmlObject->recommended_major;
    $supportedMajor = (string) $requestXmlObject->supported_majors;
    $supportedMajorVersions = explode(',', $supportedMajor);

    $releaseVersions = array();
    foreach ($requestXmlObject->releases->release as $release) {
      if (in_array($release->version_major, $supportedMajorVersions)) {
        $releaseVersions[] = $release;
        $key = array_search($release->version_major, $supportedMajorVersions);
        unset($supportedMajorVersions[$key]);
      }
      if (count($supportedMajorVersions) < 1) {
        break;
      }
    }

    $versions = array();
    foreach ($releaseVersions as $release) {
      $isSecurityRelease = FALSE;
      if (isset($release->terms)) {
        foreach ($release->terms->term as $term) {
          if (strtolower($term->value) == 'security update') {
            $isSecurityRelease = TRUE;
            break;
          }
        }
      }

      $versionType = ($release->version_major == $recommendedMajorVersion) ? 'recommended' : 'other';

      $versions[$versionType] = array(
        'version' => (string) $release->version,
        'isSecurity' => $isSecurityRelease,
      );
    }

    $this->moduleVersions = $versions;
    $this->moduleName = (string) $requestXmlObject->title;
  }

  /**
   * @return mixed
   */
  protected function getRequestUrl() {
    return 'http://updates.drupal.org/release-history/' . $this->moduleRequestName . '/' . $this->moduleRequestVersion;
  }


}