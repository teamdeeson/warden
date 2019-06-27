<?php

namespace Deeson\WardenDrupalBundle\Document;

use Deeson\WardenBundle\Document\BaseDocument;
use Deeson\WardenBundle\Document\SiteDocument;
use Deeson\WardenBundle\Exception\DocumentNotFoundException;
use Deeson\WardenDrupalBundle\Managers\DrupalModuleManager;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(
 *     collection="sites_modules"
 * )
 */
class SiteModuleDocument extends BaseDocument {

  /**
   * @Mongodb\Field(type="string")
   */
  protected $siteId;

  /**
   * @Mongodb\Field(type="collection")
   */
  protected $modules;

  /**
   * @return mixed
   */
  public function getSiteId() {
    return $this->siteId;
  }

  /**
   * @param mixed $siteId
   */
  public function setSiteId($siteId) {
    $this->siteId = $siteId;
  }

  /**
   * Get the site modules.
   *
   * @return mixed
   */
  public function getModules() {
    return (!empty($this->modules)) ? $this->modules : array();
  }

  /**
   * Set the current modules for the site.
   *
   * @param array $modules
   *   List of modules to add to the site.
   * @param bool $update
   *   If true, update the site module versions while using the existing version
   *   information.
   */
  public function setModules($modules, $update = FALSE) {
    $currentModules = ($update) ? $this->getModules() : array();
    $currentVersions = array();
    if (!empty($currentModules)) {
      foreach ($currentModules as $value) {
        $currentVersions[$value['name']] = $value;
      }
    }

    $moduleList = array();
    foreach ($modules as $name => $version) {
      $module = array(
        'name' => $name,
        'version' => $version['version'],
        /*'version' => array(
          'current' => $version['version'],
          'latest' => '',
          'isSecurity' => 0,
        ),*/
      );

      // Set the current version if there was one.
      if (isset($currentVersions[$name])) {
        if (isset($currentVersions[$name]['latestVersion'])) {
          $module['latestVersion'] = $currentVersions[$name]['latestVersion'];
        }
        if (isset($currentVersions[$name]['isSecurity'])) {
          $module['isSecurity'] = $currentVersions[$name]['isSecurity'];
        }
      }

      $drupalVersion = DrupalModuleDocument::getMajorVersion($module['version']);
      $moduleVersions = $version['latestVersion'][$drupalVersion];

      $module['isUnsupported'] = DrupalModuleDocument::isVersionUnsupported($moduleVersions, $module);
      $moduleList[$name] = $module;
    }
    ksort($moduleList);
    $this->modules = $moduleList;
  }

  /**
   * Gets a modules latest version for the site.
   *
   * @param $module
   *
   * @return string
   */
  public function getModuleLatestVersion($module) {
    return (!isset($module['latestVersion'])) ? '' : $module['latestVersion'];
  }

  /**
   * Returns if the provided module is an unsupported release.
   *
   * @param array $module
   *
   * @return boolean
   */
  public function isModuleSupported($module) {
    return (!isset($module['isUnsupported'])) ? FALSE : $module['isUnsupported'];
  }

  /**
   * Returns if the provided module has a security release.
   *
   * @param array $module
   *
   * @return boolean
   */
  public function getModuleIsSecurity($module) {
    if ($this->getModuleIsDevRelease($module)) {
      return FALSE;
    }
    return (!isset($module['isSecurity'])) ? FALSE : $module['isSecurity'];
  }

  /**
   * Determines if the module version is a dev release or not.
   *
   * @param array $module
   *
   * @return bool
   */
  public function getModuleIsDevRelease($module) {
    return DrupalModuleDocument::isDevRelease($module['version']);
  }

  /**
   * Sets the latest versions of each of the modules for the site.
   *
   * @param $moduleLatestVersions
   */
  public function setModulesLatestVersion($moduleLatestVersions) {
    $siteModuleList = $this->getModules();
    foreach ($siteModuleList as $key => $module) {
      if (!isset($moduleLatestVersions[$module['name']])) {
        continue;
      }
      $moduleVersions = $moduleLatestVersions[$module['name']];

      $versionType = DrupalModuleDocument::MODULE_VERSION_TYPE_RECOMMENDED;
      if (isset($moduleVersions[DrupalModuleDocument::MODULE_VERSION_TYPE_OTHER])) {
        $latestVersion = DrupalModuleDocument::getRelevantLatestVersion($module['version'], $moduleVersions[DrupalModuleDocument::MODULE_VERSION_TYPE_OTHER]['version']);
        if ($latestVersion) {
          $versionType = DrupalModuleDocument::MODULE_VERSION_TYPE_OTHER;
        }
      }

      $siteModuleList[$key]['isUnsupported'] = DrupalModuleDocument::isVersionUnsupported($moduleVersions, $module);

      if (!isset($moduleVersions[$versionType])) {
        print "ERROR : module (" . $module['name'] .") version is not valid: " . print_r(array($versionType, $moduleVersions), TRUE);
      }
      else {
        /*$siteModuleList[$key] += array(
          'latestVersion' => $moduleVersions[$versionType]['version'],
          'isSecurity' => $moduleVersions[$versionType]['isSecurity'],
        );*/
        $siteModuleList[$key]['latestVersion'] = $moduleVersions[$versionType]['version'];
        $siteModuleList[$key]['isSecurity'] = $moduleVersions[$versionType]['isSecurity'];
      }
    }
    $this->modules = $siteModuleList;
  }

  /**
   * Updates a specific module on a site with version and/or security info.
   *
   * @param string $moduleName
   *   The module project name.
   * @param array $moduleData
   *   An array of the module data, keyed with version and isSecurity.
   */
  public function updateModule($moduleName, $moduleData) {
    $siteModuleList = $this->getModules();
    foreach ($siteModuleList as $key => $module) {
      if ($moduleName != $module['name']) {
        continue;
      }

      if (isset($moduleData['version'])) {
        $siteModuleList[$key]['latestVersion'] = $moduleData['version'];
      }
      if (isset($moduleData['isSecurity'])) {
        $siteModuleList[$key]['isSecurity'] = $moduleData['isSecurity'];
      }
    }
    $this->modules = $siteModuleList;
  }

  /**
   * Updates the modules list for the provided site.
   *
   * This updates the list of modules that this site has with the module documents.
   *
   * @param DrupalModuleManager $moduleManager
   * @param SiteDocument $site
   *
   * @throws DocumentNotFoundException
   */
  public function updateModules(DrupalModuleManager $moduleManager, SiteDocument $site) {
    foreach ($this->getModules() as $siteModule) {
      /** @var DrupalModuleDocument $module */
      $module = $moduleManager->findByProjectName($siteModule['name']);
      if (empty($module)) {
        print "Error getting module [{$siteModule['name']}]\n";
        continue;
      }
      $moduleSites = $module->getSites();

      // Check if the site URL is already in the list for this module.
      $alreadyExists = FALSE;
      if (is_array($moduleSites)) {
        foreach ($moduleSites as $moduleSite) {
          if ($moduleSite['id'] == $site->getId()) {
            $alreadyExists = TRUE;
            break;
          }
        }
      }

      if ($alreadyExists) {
        $module->updateSite($site->getId(), $siteModule['version']);
      }
      else {
        $module->addSite($site->getId(), $site->getName(), $site->getUrl(), $siteModule['version']);
      }
      $moduleManager->updateDocument();
    }
  }

  /**
   * Get a list of site modules that require updating.
   *
   * @return array
   */
  public function getModulesRequiringUpdates() {
    $siteModuleList = $this->getModules();
    $modulesList = array();
    foreach ($siteModuleList as $module) {
      if (!isset($module['latestVersion'])) {
        continue;
      }
      if (is_null($module['version'])) {
        continue;
      }
      if (DrupalModuleDocument::isLatestVersion($module)) {
        continue;
      }

      $severity = 1;
      if (isset($module['isSecurity'])) {
        $severity = !$module['isSecurity'];
      }

      $modulesList[$severity][] = $module;
    }
    ksort($modulesList);

    $modulesForUpdating = array();
    foreach ($modulesList as $severity => $moduleSeverity) {
      foreach ($moduleSeverity as $module) {
        $modulesForUpdating[$severity.$module['name']] = $module;
      }
    }
    ksort($modulesForUpdating);

    return $modulesForUpdating;
  }

}
