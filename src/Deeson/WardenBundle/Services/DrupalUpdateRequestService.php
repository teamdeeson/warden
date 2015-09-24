<?php

namespace Deeson\WardenBundle\Services;

use Buzz\Browser;
use Deeson\WardenBundle\Document\ModuleDocument;
use Buzz\Exception\ClientException;
use Deeson\WardenBundle\Document\SiteDocument;
use Deeson\WardenBundle\Managers\ModuleManager;
use Deeson\WardenBundle\Managers\SiteManager;
use Monolog\Logger;

class DrupalUpdateRequestService {

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
   * @var ModuleManager
   */
  protected $drupalModuleManager;

  /**
   * @var Logger
   */
  protected $logger;

  /**
   * @var Browser
   */
  protected $buzz;

  /**
   * @var SiteManager
   */
  protected $siteManager;

  /**
   * @param Browser $buzz
   * @param SiteManager $siteManager
   * @param ModuleManager $drupalModuleManager
   * @param Logger $logger
   */
  public function __construct(Browser $buzz, SiteManager $siteManager, ModuleManager $drupalModuleManager, Logger $logger) {
    $this->drupalModuleManager = $drupalModuleManager;
    $this->buzz = $buzz;
    $this->logger = $logger;
    $this->siteManager = $siteManager;
  }

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
    $this->buzz->getClient()->setTimeout(30);

    try {
      //$startTime = $this->getMicrotimeFloat();

      // Don't verify SSL certificate.
      $this->buzz->getClient()->setVerifyPeer(FALSE);

      $url = $this->getRequestUrl();

      $request = $this->buzz->get($url);
      // @todo check request header, if not 200 throw exception.
      /*$headers = $request->getHeaders();
      if (trim($headers[0]) !== 'HTTP/1.0 200 OK') {
        print 'invalid response'."\n";
        print_r($headers);
        //return;
      }*/
      $requestData = $request->getContent();

      //$endTime = $this->getMicrotimeFloat();
      //$this->requestTime = $endTime - $startTime;

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
      throw new \Exception('Error getting data for module: ' . $this->moduleRequestName);
    }

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
          //$versionInfo = ModuleDocument::getVersionInfo($release->version);
          // If the version info extra data is set than this must be a non-stable
          // release (alpha, beta, rc, bug etc).
          /*if (!is_null($versionInfo['extra'])) {
            continue;
          }*/

          $releaseVersions[] = $release;
          unset($supportedMajorVersions[$key]);
        }
        /*if (count($supportedMajorVersions) < 1) {
          break;
        }*/
      }
      else {
        // This isn't a supported version, so just return the latest release.
        $releaseVersions[] = $release;
        //break;
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
          }
        }
      }

      $versionType = ($release->version_major == $recommendedMajorVersion) ?
        ModuleDocument::MODULE_VERSION_TYPE_RECOMMENDED :
        ModuleDocument::MODULE_VERSION_TYPE_OTHER;

      $versions[$versionType][] = array(
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

  /**
   * Get the microtime.
   *
   * @return float
   */
  protected function getMicrotimeFloat() {
    list($usec, $sec) = explode(' ', microtime());
    return ((float)$usec + (float)$sec);
  }

  /**
   * Event: Triggered on cron runs.
   */
  public function onWardenCron() {
    $this->updateAllDrupalModules(FALSE);
  }

  /**
   * Update the Drupal Core and Modules with the latest versions.
   *
   * @param bool $updateNewSitesOnly
   *   Only update modules on sites marked as new.
   */
  public function updateAllDrupalModules($updateNewSitesOnly = FALSE) {
    $this->logger->addInfo('*** Starting Drupal Update Request Service ***');

    $moduleLatestVersion = array();
    $majorVersions = $this->siteManager->getAllMajorVersionReleases();

    foreach ($majorVersions as $version) {
      $modules = $this->drupalModuleManager->getAllByVersion($version);

      /** @var ModuleDocument $module */
      foreach ($modules as $module) {
        $this->logger->addInfo('Updating - ' . $module->getProjectName() . ' for version: ' . $version);

        try {
          $this->processDrupalUpdateData($module->getProjectName(), $version);
        } catch (\Exception $e) {
          $this->logger->addWarning(' - Unable to update module version [' . $version . ']: ' . $e->getMessage());
          continue;
        }

        $drupalModuleVersions = $this->moduleVersions;
        $moduleVersions = array();
        // Get the recommended module version.
        if (isset($drupalModuleVersions[ModuleDocument::MODULE_VERSION_TYPE_RECOMMENDED])) {
          $moduleRecommendedLatestVersion = $drupalModuleVersions[ModuleDocument::MODULE_VERSION_TYPE_RECOMMENDED][0];
          $moduleVersions[ModuleDocument::MODULE_VERSION_TYPE_RECOMMENDED] = $moduleRecommendedLatestVersion;
          $moduleLatestVersion[$version][$module->getProjectName()][ModuleDocument::MODULE_VERSION_TYPE_RECOMMENDED] = $moduleRecommendedLatestVersion;
        }
        // Get the other module version.
        if (isset($drupalModuleVersions[ModuleDocument::MODULE_VERSION_TYPE_OTHER])) {
          $moduleOtherLatestVersion = $drupalModuleVersions[ModuleDocument::MODULE_VERSION_TYPE_OTHER][0];
          $moduleVersions[ModuleDocument::MODULE_VERSION_TYPE_OTHER] = $moduleOtherLatestVersion;
          $moduleLatestVersion[$version][$module->getProjectName()][ModuleDocument::MODULE_VERSION_TYPE_OTHER] = $moduleOtherLatestVersion;
        }

        $module->setName($this->getModuleName());
        $module->setIsNew(FALSE);
        $module->setLatestVersion($version, $moduleVersions);
        $module->setProjectStatus($this->projectStatus);
        $this->drupalModuleManager->updateDocument();
      }
    }

    foreach ($majorVersions as $version) {
      // Update the core after the modules to update the versions of the modules
      // for a site.
      $this->logger->addInfo('Updating - Drupal version: ' . $version);

      try {
        $this->processDrupalUpdateData('drupal', $version);
      } catch (\Exception $e) {
        $this->logger->addWarning(' - Unable to update drupal version [' . $version . ']: ' . $e->getMessage());
        continue;
      }

      $newOnly = ($updateNewSitesOnly) ? array('isNew' => TRUE) : array();
      $sites = $this->siteManager->getDocumentsBy(array_merge(array('coreVersion.release' => $version), $newOnly));

      // Update the sites for the major version with the latest core & module version information.
      $moduleVersions = $this->moduleVersions[ModuleDocument::MODULE_VERSION_TYPE_RECOMMENDED];

      /** @var SiteDocument $site */
      foreach ($sites as $site) {
        $this->logger->addInfo('Updating site: ' . $site->getId() . ' - ' . $site->getUrl());

        if ($site->getCoreReleaseVersion() != $version) {
          continue;
        }

        if (isset($moduleLatestVersion[$version])) {
          $site->setModulesLatestVersion($moduleLatestVersion[$version]);
        }

        $siteCurrentVersion = $site->getCoreVersion();
        $hasCriticalIssue = $site->getIsSecurityCoreVersion();
        $needsSecurityUpdate = FALSE;
        foreach ($moduleVersions as $moduleVersion) {
          if ($moduleVersion['version'] < $siteCurrentVersion) {
            break;
          }

          if ($moduleVersion['isSecurity']) {
            $needsSecurityUpdate = TRUE;
            $hasCriticalIssue = TRUE;
          }
        }

        $site->setLatestCoreVersion($moduleVersions[0]['version'], $needsSecurityUpdate);
        $site->setIsNew(FALSE);
        $site->setHasCriticalIssue($hasCriticalIssue);
        $this->siteManager->updateDocument();
      }
    }

    $this->logger->addInfo('*** FINISHED Drupal Update Request Service ***');
  }

  /**
   * Gets the latest information on a module from Drupal.org.
   *
   * @param string $moduleName
   * @param int $version
   *
   * @throws \Exception
   */
  protected function processDrupalUpdateData($moduleName, $version) {
    $this->setModuleRequestName($moduleName);
    $this->setModuleRequestVersion($version);
    $this->processRequest();

    $this->moduleVersions = $this->getModuleVersions();
    $this->projectStatus = $this->getProjectStatus();
  }

}