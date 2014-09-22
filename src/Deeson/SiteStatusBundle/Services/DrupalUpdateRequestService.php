<?php

namespace Deeson\SiteStatusBundle\Services;


use Deeson\SiteStatusBundle\Document\ModuleDocument;

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
   * @var string
   */
  protected $projectStatus;

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
   * @return string
   */
  public function getProjectStatus() {
    return $this->projectStatus;
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

    $projectStatus = (string) $requestXmlObject->project_status;

    $recommendedMajorVersion = 0;
    $supportedMajorVersions = array();
    if (isset($requestXmlObject->supported_majors)) {
      $recommendedMajorVersion = (string) $requestXmlObject->recommended_major;
      $supportedMajor = (string) $requestXmlObject->supported_majors;
      $supportedMajorVersions = explode(',', $supportedMajor);
    }

    $releaseVersions = array();
    foreach ($requestXmlObject->releases->release as $release) {
      if (count($supportedMajorVersions) > 0) {
        if (in_array($release->version_major, $supportedMajorVersions)) {
          $releaseVersions[] = $release;
          $key = array_search($release->version_major, $supportedMajorVersions);
          unset($supportedMajorVersions[$key]);
        }
        if (count($supportedMajorVersions) < 1) {
          break;
        }
      }
      else {
        // This isn't a supported version, so just return the latest release.
        $releaseVersions[] = $release;
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

      /*if ($projectStatus != ModuleDocument::MODULE_PROJECT_STATUS_PUBLISHED) {
        $versionType = $projectStatus;
      }
      else {*/
        $versionType = ($release->version_major == $recommendedMajorVersion) ?
          ModuleDocument::MODULE_VERSION_TYPE_RECOMMENDED :
          ModuleDocument::MODULE_VERSION_TYPE_OTHER;
      //}

      $versions[$versionType] = array(
        'version' => (string) $release->version,
        'isSecurity' => $isSecurityRelease,
      );
    }

    $this->projectStatus = $projectStatus;
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