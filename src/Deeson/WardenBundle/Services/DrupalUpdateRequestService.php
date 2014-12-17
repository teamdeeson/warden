<?php

namespace Deeson\WardenBundle\Services;

use Deeson\WardenBundle\Document\ModuleDocument;
use Buzz\Exception\ClientException;

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
   * {@InheritDoc}
   */
  public function processRequest() {
    $this->setClientTimeout($this->connectionTimeout);

    try {
      $startTime = $this->getMicrotimeFloat();

      // Don't verify SSL certificate.
      $this->buzz->getClient()->setVerifyPeer(FALSE);

      $url = $this->getRequestUrl();

      $request = $this->buzz->get($url, $this->connectionHeaders);
      // @todo check request header, if not 200 throw exception.
      /*$headers = $request->getHeaders();
      if (trim($headers[0]) !== 'HTTP/1.0 200 OK') {
        print 'invalid response'."\n";
        print_r($headers);
        //return;
      }*/
      $requestData = $request->getContent();

      $endTime = $this->getMicrotimeFloat();
      $this->requestTime = $endTime - $startTime;

      $this->processRequestData($requestData);
    }
    catch (ClientException $e) {
      throw new \Exception($e->getMessage());
    }
  }

  /**
   * Processes the data that has come back from the request.
   *
   * @param $requestData
   *   Data that has come back from the request.
   * @throws \Exception
   */
  public function processRequestData($requestData) {
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
    $latestReleaseVersions = array();
    foreach ($requestXmlObject->releases->release as $release) {
      if (count($supportedMajorVersions) > 0) {
        // Check if this major version is in the list of supported versions.
        if (in_array($release->version_major, $supportedMajorVersions)) {
          $key = array_search($release->version_major, $supportedMajorVersions);
          $latestReleaseVersions[$supportedMajorVersions[$key]][] = $release;

          // Get the version information for this release version.
          $versionInfo = ModuleDocument::getVersionInfo($release->version);
          // If the version info extra data is set than this must be a non-stable
          // release (alpha, beta, rc, bug etc).
          if (!is_null($versionInfo['extra'])) {
            continue;
          }

          $releaseVersions[] = $release;
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

    // If there is still version data available, then set the release to be the
    // latest available version as there must not be a stable version for that
    // minor release yet.
    if (count($supportedMajorVersions) > 0) {
      foreach ($supportedMajorVersions as $version) {
        $releaseVersions[] = $latestReleaseVersions[$version][0];
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