<?php

namespace Deeson\WardenDrupalBundle\Services;

use Deeson\WardenDrupalBundle\Document\DrupalModuleDocument;
use Deeson\WardenBundle\Document\SiteDocument;
use Deeson\WardenBundle\Managers\SiteManager;
use Deeson\WardenDrupalBundle\Document\SiteDrupalModuleDocument;
use Deeson\WardenDrupalBundle\Managers\DrupalModuleManager;
use Deeson\WardenDrupalBundle\Managers\SiteDrupalModuleManager;
use Monolog\Logger;

class DrupalModuleService {

  /**
   * @var DrupalModuleManager
   */
  protected $moduleManager;

  /**
   * @var Logger
   */
  protected $logger;

  /**
   * @var SiteManager
   */
  protected $siteManager;

  /**
   * @var SiteDrupalModuleManager
   */
  protected $siteModuleManager;

  /**
   * @param DrupalModuleManager $moduleManager
   * @param SiteManager $siteManager
   * @param SiteDrupalModuleManager $siteModuleManager
   * @param Logger $logger
   */
  public function __construct(DrupalModuleManager $moduleManager, SiteManager $siteManager, SiteDrupalModuleManager $siteModuleManager, Logger $logger) {
    $this->moduleManager = $moduleManager;
    $this->siteManager = $siteManager;
    $this->siteModuleManager = $siteModuleManager;
    $this->logger = $logger;
  }

  /**
   * Event: warden.cron
   *
   * Fired when cron is run to update the list of sites within each module.
   */
  public function onWardenCron() {
    $this->rebuildAllModuleSites();
  }

  /**
   * Remove all sites from each module.
   */
  public function rebuildAllModuleSites() {
    $this->removeAllModuleSites();
    $this->updateAllModuleSites();
    $this->removeUnusedModules();
  }

  /**
   * Removes all the sites referenced by all of the modules.
   */
  protected function removeAllModuleSites() {
    $modules = $this->moduleManager->getAllDocuments();
    foreach ($modules as $module) {
      /** @var DrupalModuleDocument $module */
      $module->setSites(array());
      $this->moduleManager->updateDocument();
    }
  }

  /**
   * Updates each of the modules with their associated sites.
   */
  protected function updateAllModuleSites() {
    $sites = $this->siteManager->getAllDocuments();
    foreach ($sites as $site) {
      /** @var SiteDocument $site */
      print 'Updating site modules: ' . $site->getId() . ' - ' . $site->getUrl() . "\n";
      /** @var SiteDrupalModuleDocument $siteModule */
      $siteModule = $this->siteModuleManager->findBySiteId($site->getId());
      if (empty($siteModule)) {
        continue;
      }
      $siteModule->updateModules($this->moduleManager, $site);
      $this->siteModuleManager->saveDocument($siteModule);
    }
  }

  /**
   * Removes modules that have no sites associated to them.
   */
  protected function removeUnusedModules() {
    $modules = $this->moduleManager->getUnusedModules();
    if (empty($modules)) {
      return;
    }

    foreach ($modules as $module) {
      /** @var DrupalModuleDocument $module */
      $this->logger->addInfo('Remove module "' . $module->getName() . '" as it has no sites associated to it.');
      print "Removing module \"" . $module->getName() . "\" as it has no sites associated to it.\n";
      $this->moduleManager->deleteDocument($module->getId());
    }
  }

}
