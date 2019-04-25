<?php

namespace Deeson\WardenDrupalBundle\Services;

use Deeson\WardenBundle\Document\SiteDocument;
use Deeson\WardenBundle\Event\CronEvent;
use Deeson\WardenBundle\Event\DashboardAddSiteEvent;
use Deeson\WardenBundle\Event\DashboardListEvent;
use Deeson\WardenBundle\Event\DashboardUpdateEvent;
use Deeson\WardenBundle\Event\SiteDeleteEvent;
use Deeson\WardenBundle\Event\SiteListEvent;
use Deeson\WardenBundle\Event\SiteRefreshEvent;
use Deeson\WardenBundle\Event\SiteShowEvent;
use Deeson\WardenBundle\Event\SiteUpdateEvent;
use Deeson\WardenBundle\Event\WardenEvents;
use Deeson\WardenBundle\Exception\WardenRequestException;
use Deeson\WardenBundle\Managers\SiteManager;
use Deeson\WardenBundle\Services\SiteConnectionService;
use Deeson\WardenDrupalBundle\Managers\DrupalModuleManager;
use Deeson\WardenDrupalBundle\Managers\SiteDrupalManager;
use Deeson\WardenDrupalBundle\Managers\SiteModuleManager;
use Deeson\WardenDrupalBundle\Document\SiteModuleDocument;
use Deeson\WardenDrupalBundle\Document\SiteDrupalDocument;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DrupalSiteService {

  /**
   * @var SiteManager
   */
  protected $siteManager;

  /**
   * @var DrupalModuleManager
   */
  protected $moduleManager;

  /**
   * @var SiteDrupalManager
   */
  protected $siteDrupalManager;

  /**
   * @var SiteModuleManager
   */
  protected $siteModuleManager;

  /**
   * @var SiteConnectionService
   */
  protected $siteConnectionService;

  /**
   * @var Logger
   */
  protected $logger;

  /**
   * @var EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * @param DrupalModuleManager $moduleManager
   * @param SiteDrupalManager $siteDrupalManager
   * @param SiteModuleManager $siteModuleManager
   * @param SiteManager $siteManager
   * @param SiteConnectionService $siteConnectionService
   * @param Logger $logger
   * @param EventDispatcherInterface $dispatcher
   */
  public function __construct(DrupalModuleManager $moduleManager, SiteDrupalManager $siteDrupalManager, SiteModuleManager $siteModuleManager, SiteManager $siteManager, SiteConnectionService $siteConnectionService, Logger $logger, EventDispatcherInterface $dispatcher) {
    $this->moduleManager = $moduleManager;
    $this->siteDrupalManager = $siteDrupalManager;
    $this->siteModuleManager = $siteModuleManager;
    $this->siteManager = $siteManager;
    $this->siteConnectionService = $siteConnectionService;
    $this->logger = $logger;
    $this->dispatcher = $dispatcher;
  }

  /**
   * Get the site status URL.
   *
   * @param SiteDocument $site
   *   The site being updated
   *
   * @return mixed
   */
  protected function getSiteRequestUrl(SiteDocument $site) {
    return $site->getUrl() . '/admin/reports/warden';
  }

  /**
   * Determine if the given site data refers to a Drupal site.
   *
   * @param SiteDocument $site
   * @return bool
   */
  protected function isDrupalSite(SiteDocument $site) {
    return empty($site->getType()) || $site->getType() == SiteDrupalDocument::TYPE_DRUPAL;
  }

  /**
   * Processes the data that has come back from the request.
   *
   * @param SiteDocument $site
   *   The site being updated
   * @param $data
   *   New data about the site.
   */
  public function processUpdate(SiteDocument $site, $data) {
    $moduleData = json_decode(json_encode($data->contrib), TRUE);
    if (!is_array($moduleData)) {
      $moduleData = array();
    }
    $this->moduleManager->addModules($moduleData);
    $site->setName($data->site_name);

    /** @var SiteDrupalDocument $siteDrupal */
    $siteDrupal = $this->siteDrupalManager->getBySiteId($site->getId());
    if (empty($siteDrupal)) {
      $siteDrupal = $this->siteDrupalManager->makeNewItem();
      $siteDrupal->setSiteId($site->getId());
    }
    $siteDrupal->setCoreVersion($data->core->drupal->version);
    $this->siteDrupalManager->saveDocument($siteDrupal);

    /** @var SiteModuleDocument $siteModule */
    $siteModule = $this->siteModuleManager->findBySiteId($site->getId());
    if (empty($siteModule)) {
      $siteModule = $this->siteModuleManager->makeNewItem();
      $siteModule->setSiteId($site->getId());
    }
    $siteModule->setModules($moduleData, TRUE);
    $this->siteModuleManager->saveDocument($siteModule);

    // @todo check if this is valid here, as whether the site has critical errors or not hasn't been set.
    $event = new DashboardUpdateEvent($site);
    $this->dispatcher->dispatch(WardenEvents::WARDEN_DASHBOARD_UPDATE, $event);
  }

  /**
   * Event: warden.cron
   *
   * Updates all the sites with their latest data into Warden.
   *
   * @param CronEvent $event
   */
  public function onWardenCron(CronEvent $event) {
    $sites = $event->getSites();
    foreach ($sites as $site) {
      /** @var SiteDocument $site */
      print 'Updating site: ' . $site->getId() . ' - ' . $site->getUrl() . "\n";
      $this->logger->addInfo('Updating site: ' . $site->getId() . ' - ' . $site->getUrl());

      try {
        $event = new SiteRefreshEvent($site);
        $this->dispatcher->dispatch(WardenEvents::WARDEN_SITE_REFRESH, $event);
      }
      catch (\Exception $e) {
        print 'General Error - Unable to retrieve data from the site: ' . $e->getMessage() . "\n";
        $this->logger->addError('General Error - Unable to retrieve data from the site: ' . $e->getMessage());
      }
    }
  }

  /**
   * Event: warden.site.refresh
   *
   * Fires when the Warden administrator requests for a site to be refreshed.
   *
   * @param SiteRefreshEvent $event
   *   Event detailing the site requesting a refresh.
   */
  public function onWardenSiteRefresh(SiteRefreshEvent $event) {
    $site = $event->getSite();
    if (!$this->isDrupalSite($site)) {
      return;
    }

    try {
      $this->logger->addInfo('This is the start of a Drupal Site Refresh Event: ' . $site->getUrl());
      $this->siteConnectionService->post($this->getSiteRequestUrl($site), $site);
      $event->addMessage('A Drupal site has been updated: ' . $site->getUrl());
      $this->logger->addInfo('This is the end of a Drupal Site Refresh Event: ' . $site->getUrl());
    }
    catch (WardenRequestException $e) {
      $event->addMessage($e->getMessage(), SiteRefreshEvent::WARNING);
    }
  }

  /**
   * Event: warden.site.update
   *
   * Fires when a site is updated. This will detect if the site is a Drupal site
   * and update the Drupal data accordingly.
   *
   * @param SiteUpdateEvent $event
   */
  public function onWardenSiteUpdate(SiteUpdateEvent $event) {
    if (!$this->isDrupalSite($event->getSite())) {
      return;
    }

    $this->logger->addInfo('This is the start of a Drupal Site Update Event: ' . $event->getSite()->getUrl());
    $this->processUpdate($event->getSite(), $event->getData());
    $this->logger->addInfo('This is the end of a Drupal Site Update Event: ' . $event->getSite()->getUrl());
  }

  /**
   * Event: warden.site.show
   *
   * Fires when a site page is viewed.
   *
   * @param SiteShowEvent $event
   */
  public function onWardenSiteShow(SiteShowEvent $event) {
    $site = $event->getSite();
    if (!$this->isDrupalSite($site)) {
      return;
    }

    $this->logger->addInfo('This is the start of a Drupal show site event: ' . $site->getUrl());

    // Check if Drupal core requires a security update.
    /** @var SiteDrupalDocument $siteDrupal */
    $siteDrupal = $this->siteDrupalManager->getBySiteId($site->getId());
    if (!empty($siteDrupal)) {
      $event->addTemplate('DeesonWardenDrupalBundle:Sites:siteDetails.html.twig');
      $event->addParam('coreVersion', $siteDrupal->getCoreVersion());
      $event->addParam('latestCoreVersion', $siteDrupal->getLatestCoreVersion());
      if ($siteDrupal->hasOlderCoreVersion() && $siteDrupal->getIsSecurityCoreVersion()) {
        $event->addParam('coreNeedsSecurityUpdate', $siteDrupal->getCoreVersion());
      }

      $event->addParam('coreVersion', $siteDrupal->getCoreVersion());
      $event->addParam('latestCoreVersion', $siteDrupal->getLatestCoreVersion());
      $event->addParam('logoPath', $siteDrupal->getTypeImagePath());
    }

    // Check if there are any Drupal modules that require updates.
    /** @var SiteModuleDocument $siteModule */
    $siteModule = $this->siteModuleManager->findBySiteId($site->getId());
    if (!empty($siteModule)) {
      $modulesRequiringUpdates = $siteModule->getModulesRequiringUpdates();
      $event->addParam('siteModule', $siteModule);

      $securityCount = 0;
      $updateCount = 0;
      foreach ($modulesRequiringUpdates as $module) {
        if ($siteModule->getModuleIsSecurity($module)) {
          $securityCount++;
          continue;
        }
        $updateCount++;
      }

      if (!empty($modulesRequiringUpdates)) {
        $event->addTabTemplate('modules', 'DeesonWardenDrupalBundle:Sites:moduleUpdates.html.twig');
        $event->addParam('modulesRequiringUpdates', $modulesRequiringUpdates);
        $event->addParam('modulesRequiringSecurityUpdatesCount', $securityCount);
      }

      // List the Drupal modules that used on the site.
      $event->addTabTemplate('modules', 'DeesonWardenDrupalBundle:Sites:modules.html.twig');
      $event->addParam('modules', $siteModule->getModules());
      $event->addParam('modulesRequiringUpdatesCount', $updateCount);
    }

    $this->logger->addInfo('This is the end of a Drupal show site event: ' . $site->getUrl());
  }

  /**
   * Event: warden.site.list
   *
   * Fires when the sites are listed.
   *
   * @param SiteListEvent $event
   */
  public function onWardenSiteList(SiteListEvent $event) {
    $site = $event->getSite();
    if (!$this->isDrupalSite($site)) {
      return;
    }

    /** @var SiteDrupalDocument $drupalSite */
    $drupalSite = $this->siteDrupalManager->getBySiteId($site->getId());
    if (!empty($drupalSite)) {
      $event->setSiteTypeLogoPath($drupalSite->getTypeImagePath());
    }
  }

  /**
   * Event warden.dashboard.list
   *
   * Fires when the dashboard of sites are listed.
   *
   * @param DashboardListEvent $event
   */
  public function onWardenDashboardList(DashboardListEvent $event) {
    $dashboardSite = $event->getSite();
    /** @var SiteDocument $site */
    $site = $this->siteManager->getDocumentById($dashboardSite->getSiteId());
    if (!$this->isDrupalSite($site)) {
      return;
    }

    /** @var SiteDrupalDocument $drupalSite */
    $drupalSite = $this->siteDrupalManager->getBySiteId($site->getId());
    if (!empty($drupalSite)) {
      $event->setSiteTypeLogoPath($drupalSite->getTypeImagePath());
    }
  }

  /**
   * Event: warden.site.delete
   *
   * Fires when a site is deleted.
   *
   * @param SiteDeleteEvent $event
   */
  public function onWardenSiteDelete(SiteDeleteEvent $event) {
    $site = $event->getSite();
    if (!$this->isDrupalSite($site)) {
      return;
    }

    /** @var SiteDrupalDocument $siteDrupal */
    $siteDrupal = $this->siteDrupalManager->getBySiteId($site->getId());
    $this->siteDrupalManager->deleteDocument($siteDrupal->getId());

    /** @var SiteModuleDocument $siteModule */
    $siteModule = $this->siteModuleManager->findBySiteId($site->getId());
    $this->siteModuleManager->deleteDocument($siteModule->getId());
  }

  /**
   * Event: warden.dashboard.add_site
   *
   * Fires when a site is added to the dashboard.
   *
   * @param DashboardAddSiteEvent $event
   */
  public function onWardenDashboardAddSite(DashboardAddSiteEvent $event) {
    $site = $event->getSite();
    $modulesHaveSecurityUpdate = [];

    /** @var SiteDrupalDocument $siteDrupal */
    $siteDrupal = $this->siteDrupalManager->getBySiteId($site->getId());
    if (empty($siteDrupal)) {
      $event->setIssues(['*No Drupal site modules could be found - this site needs refreshing*']);
      return;
    }

    // Check if Core is out of date.
    if ($siteDrupal->getIsSecurityCoreVersion()) {
      $modulesHaveSecurityUpdate[] = 'Drupal Core';
    }

    /** @var SiteModuleDocument $siteModule */
    $siteModule = $this->siteModuleManager->findBySiteId($site->getId());

    // Get a list of modules that have security updates.
    $moduleUpdates = $siteModule->getModulesRequiringUpdates();
    foreach ($moduleUpdates as $module) {
      if (!$module['isSecurity']) {
        continue;
      }
      $modulesHaveSecurityUpdate[] = $module['name'];
    }
    sort($modulesHaveSecurityUpdate);

    $event->setIssues($modulesHaveSecurityUpdate);
  }

  /**
   * Get the current micro time.
   *
   * @return float
   */
  protected function getMicroTimeFloat() {
    list($microSeconds, $seconds) = explode(' ', microtime());
    return ((float) $microSeconds + (float) $seconds);
  }
}
