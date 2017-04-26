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
   * @var array
   */
  protected $drupalAllModuleVersions = array();

  /**
   * @var array
   */
  protected $moduleLatestVersion = array();

  /**
   * @var array
   */
  protected $majorVersions = array();

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
        }
      }
    }

    $versions = array();
    foreach ($latestReleaseVersions as $releaseMajorVersion => $releaseVersions) {
      foreach ($releaseVersions as $release) {
        $isSecurityRelease = FALSE;
        if (isset($release->terms)) {
          foreach ($release->terms->term as $term) {
            if (strtolower($term->value) == 'security update') {
              $isSecurityRelease = TRUE;
            }
          }
        }

        $versionType = (isset($release->version_major) && $release->version_major == $recommendedMajorVersion) ?
          ModuleDocument::MODULE_VERSION_TYPE_RECOMMENDED :
          ModuleDocument::MODULE_VERSION_TYPE_OTHER;

        $versions[$versionType][] = array(
          'version' => isset($release->version) ? (string) $release->version : 0,
          'isSecurity' => $isSecurityRelease,
        );
      }
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
    $this->updateAllDrupalModules();
  }

  /**
   * Update the Drupal Core and Modules with the latest versions.
   *
   * @param bool $updateNewSitesOnly
   *   Only update modules on sites marked as new.
   */
  public function updateAllDrupalModules($updateNewSitesOnly = FALSE) {
    $this->logger->addInfo('*** Starting Drupal Update Request Service ***');

    $this->majorVersions = $this->siteManager->getAllMajorVersionReleases();

    $this->updateContribModules();
    $this->updateCore($updateNewSitesOnly);

    $this->logger->addInfo('*** FINISHED Drupal Update Request Service ***');
  }

  /**
   * Get the contrib module version information.
   */
  protected function updateContribModules() {
    foreach ($this->majorVersions as $version) {
      $modules = $this->drupalModuleManager->getAllByVersion($version);

      /** @var ModuleDocument $module */
      foreach ($modules as $module) {
        $this->logger->addInfo('Updating - ' . $module->getProjectName() . ' for version: ' . $version);

        try {
          $this->processDrupalUpdateData($module->getProjectName(), $version);
        }
        catch (\Exception $e) {
          $this->logger->addWarning(' - Unable to update module version [' . $version . ']: ' . $e->getMessage());
          continue;
        }

        $this->drupalAllModuleVersions[$version][$module->getProjectName()] = $drupalModuleVersions = $this->moduleVersions;
        $moduleVersions = array();
        // Get the recommended module version.
        if (isset($drupalModuleVersions[ModuleDocument::MODULE_VERSION_TYPE_RECOMMENDED])) {
          $moduleRecommendedLatestVersion = $drupalModuleVersions[ModuleDocument::MODULE_VERSION_TYPE_RECOMMENDED][0];
          $moduleVersions[ModuleDocument::MODULE_VERSION_TYPE_RECOMMENDED] = $moduleRecommendedLatestVersion;
          $this->moduleLatestVersion[$version][$module->getProjectName()][ModuleDocument::MODULE_VERSION_TYPE_RECOMMENDED] = $moduleRecommendedLatestVersion;
        }
        // Get the other module version.
        if (isset($drupalModuleVersions[ModuleDocument::MODULE_VERSION_TYPE_OTHER])) {
          $moduleOtherLatestVersion = $drupalModuleVersions[ModuleDocument::MODULE_VERSION_TYPE_OTHER][0];
          $moduleVersions[ModuleDocument::MODULE_VERSION_TYPE_OTHER] = $moduleOtherLatestVersion;
          $this->moduleLatestVersion[$version][$module->getProjectName()][ModuleDocument::MODULE_VERSION_TYPE_OTHER] = $moduleOtherLatestVersion;
        }

        $module->setName($this->getModuleName());
        $module->setIsNew(FALSE);
        $module->setLatestVersion($version, $moduleVersions);
        $module->setProjectStatus($this->projectStatus);
        $this->drupalModuleManager->updateDocument();
      }
    }
  }

  /**
   * Get Core version information.
   *
   * @param $updateNewSitesOnly
   */
  protected function updateCore($updateNewSitesOnly) {
    foreach ($this->majorVersions as $version) {
      // Update the core after the modules to update the versions of the modules
      // for a site.
      $this->logger->addInfo('Updating - Drupal version: ' . $version);

      try {
        $this->processDrupalUpdateData('drupal', $version);
      }
      catch (\Exception $e) {
        $this->logger->addWarning(' - Unable to update drupal version [' . $version . ']: ' . $e->getMessage());
        continue;
      }

      $newOnly = ($updateNewSitesOnly) ? array('isNew' => TRUE) : array();
      $sites = $this->siteManager->getDocumentsBy(array_merge(array('coreVersion.release' => $version), $newOnly));

      // Update the sites for the major version with the latest core & module version information.
      $coreVersions = isset($this->moduleVersions[ModuleDocument::MODULE_VERSION_TYPE_RECOMMENDED]) ? $this->moduleVersions[ModuleDocument::MODULE_VERSION_TYPE_RECOMMENDED] : NULL;

      // Check if this is an array and skip on if not.
      if (!is_array($coreVersions)) {
        continue;
      }

      /** @var SiteDocument $site */
      foreach ($sites as $site) {
        $this->logger->addInfo('Updating site: ' . $site->getId() . ' - ' . $site->getUrl());

        if ($site->getCoreReleaseVersion() != $version) {
          continue;
        }

        if (isset($this->moduleLatestVersion[$version])) {
          $site->setModulesLatestVersion($this->moduleLatestVersion[$version]);
        }

        // Check for if the core version is out of date and requires a security update.
        $siteCurrentVersion = $site->getCoreVersion();
        $hasCriticalIssue = FALSE;
        $needsSecurityUpdate = FALSE;
        foreach ($coreVersions as $coreVersion) {
          if ($coreVersion['version'] == $siteCurrentVersion) {
            break;
          }

          if ($coreVersion['isSecurity']) {
            $needsSecurityUpdate = TRUE;
            $hasCriticalIssue = TRUE;
          }
        }

        // Check all the site modules to see if any of them are out of date and need a security update.
        foreach ($site->getModules() as $siteModule) {
          if (!isset($siteModule['latestVersion'])) {
            continue;
          }
          if ($siteModule['version'] == $siteModule['latestVersion']) {
            continue;
          }
          if (is_null($siteModule['version'])) {
            continue;
          }

          // Check to see if this site's modules require a security update.
          $siteModuleVersionInfo = ModuleDocument::getVersionInfo($siteModule['version']);
          $moduleVersionInfo = $this->drupalAllModuleVersions[$version][$siteModule['name']];

          $versionType = NULL;
          if (isset($moduleVersionInfo[ModuleDocument::MODULE_VERSION_TYPE_OTHER])) {
            $drupalModuleOtherVersionInfo = ModuleDocument::getVersionInfo($moduleVersionInfo[ModuleDocument::MODULE_VERSION_TYPE_OTHER][0]['version']);
            $versionType = $drupalModuleOtherVersionInfo['minor'] == $siteModuleVersionInfo['minor'] ? ModuleDocument::MODULE_VERSION_TYPE_OTHER : NULL;
          }
          if (isset($moduleVersionInfo[ModuleDocument::MODULE_VERSION_TYPE_RECOMMENDED]) && is_null($versionType)) {
            $drupalModuleRecommendedVersionInfo = ModuleDocument::getVersionInfo($moduleVersionInfo[ModuleDocument::MODULE_VERSION_TYPE_RECOMMENDED][0]['version']);
            $versionType = $drupalModuleRecommendedVersionInfo['minor'] >= $siteModuleVersionInfo['minor'] ? ModuleDocument::MODULE_VERSION_TYPE_RECOMMENDED : NULL;
          }

          if (!is_null($versionType)) {
            foreach ($moduleVersionInfo[$versionType] as $drupalModule) {
              if ($drupalModule['version'] == $siteModule['version']) {
                break;
              }

              // Check for site module being a dev version - then skip.
              if (isset($siteModuleVersionInfo['extra']) && strstr($siteModuleVersionInfo['extra'], 'dev') !== FALSE) {
                break;
              }

              if ($drupalModule['isSecurity']) {
                unset($drupalModule['version']);
                $site->updateModule($siteModule['name'], $drupalModule);
                $siteModule['isSecurity'] = TRUE;
              }
            }
          }

          if ($siteModule['isSecurity']) {
            $hasCriticalIssue = TRUE;
          }
        }

        $site->setLatestCoreVersion($coreVersions[0]['version'], $needsSecurityUpdate);
        $site->setIsNew(FALSE);
        $site->setHasCriticalIssue($hasCriticalIssue);
        $this->siteManager->updateDocument();
      }
    }
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
