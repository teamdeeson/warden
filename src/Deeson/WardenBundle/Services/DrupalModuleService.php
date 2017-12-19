<?php

namespace Deeson\WardenBundle\Services;

use Deeson\WardenBundle\Document\ModuleDocument;
use Deeson\WardenBundle\Document\SiteDocument;
use Deeson\WardenBundle\Exception\DocumentNotFoundException;
use Deeson\WardenBundle\Managers\ModuleManager;
use Deeson\WardenBundle\Managers\SiteManager;
use Monolog\Logger;

class DrupalModuleService {

  /**
   * @var ModuleManager
   */
  protected $drupalModuleManager;

  /**
   * @var Logger
   */
  protected $logger;

  /**
   * @var SiteManager
   */
  protected $siteManager;

  /**
   * @param ModuleManager $drupalModuleManager
   * @param SiteManager $siteManager
   * @param Logger $logger
   */
  public function __construct(ModuleManager $drupalModuleManager, SiteManager $siteManager, Logger $logger) {
    $this->drupalModuleManager = $drupalModuleManager;
    $this->siteManager = $siteManager;
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
    $modules = $this->drupalModuleManager->getAllDocuments();
    foreach ($modules as $module) {
      /** @var ModuleDocument $module */
      $module->setSites(array());
      $this->drupalModuleManager->updateDocument();
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
      $site->updateModules($this->drupalModuleManager);
    }
  }

  /**
   * Removes modules that have no sites associated to them.
   */
  protected function removeUnusedModules() {
    $modules = $this->drupalModuleManager->getUnusedModules();
    if (empty($modules)) {
      return;
    }

    foreach ($modules as $module) {
      /** @var ModuleDocument $module */
      $this->logger->addInfo('Remove module "' . $module->getName() . '" as it has no sites associated to it.');
      print "Remove module \"" . $module->getName() . "\" as it has no sites associated to it.\n";
      $this->drupalModuleManager->deleteDocument($module->getId());
    }
  }

}
