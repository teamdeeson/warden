<?php

namespace Deeson\WardenBundle\Services;

use Deeson\WardenBundle\Document\SiteDocument;
use Deeson\WardenBundle\Event\DashboardUpdateEvent;
use Deeson\WardenBundle\Event\SiteEvent;
use Deeson\WardenBundle\Event\SiteShowEvent;
use Deeson\WardenBundle\Event\SiteUpdateEvent;
use Deeson\WardenBundle\Event\WardenEvents;
use Deeson\WardenBundle\Exception\DocumentNotFoundException;
use Deeson\WardenBundle\Managers\ModuleManager;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class WardenDrupalSiteService {

  /**
   * @var ModuleManager
   */
  protected $drupalModuleManager;

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
   * @param ModuleManager $drupalModuleManager
   * @param SiteConnectionService $siteConnectionService
   * @param Logger $logger
   * @param EventDispatcherInterface $dispatcher
   */
  public function __construct(ModuleManager $drupalModuleManager, SiteConnectionService $siteConnectionService, Logger $logger, EventDispatcherInterface $dispatcher) {
    $this->drupalModuleManager = $drupalModuleManager;
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
    // @TODO how to determine?
    return TRUE;
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
    $this->drupalModuleManager->addModules($moduleData);
    $site->setName($data->site_name);
    $site->setCoreVersion($data->core->drupal->version);
    $site->setModules($moduleData, TRUE);

    try {
      $site->updateModules($this->drupalModuleManager);

      $event = new DashboardUpdateEvent($site);
      $this->dispatcher->dispatch(WardenEvents::WARDEN_DASHBOARD_UPDATE, $event);
    }
    catch (DocumentNotFoundException $e) {
      $this->logger->addWarning($e->getMessage());
    }
  }

  /**
   * Event: warden.site.refresh
   *
   * Fires when the Warden administrator requests for a site to be refreshed.
   *
   * @param SiteEvent $event
   *   Event detailing the site requesting a refresh.
   */
  public function onRefreshSiteRequest(SiteEvent $event) {
    $site = $event->getSite();
    if (!$this->isDrupalSite($site)) {
      return;
    }

    $this->logger->addInfo('This is the start of a Drupal Site Refresh Event: ' . $site->getUrl());
    $this->siteConnectionService->post($this->getSiteRequestUrl($site), $site);
    $event->addMessage('A Drupal site has been updated: ' . $site->getUrl());
    $this->logger->addInfo('This is the end of a Drupal Site Refresh Event: ' . $site->getUrl());
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
   * @param SiteShowEvent $event
   */
  public function onWardenSiteShow(SiteShowEvent $event) {
    $site = $event->getSite();
    if (!$this->isDrupalSite($site)) {
      return;
    }

    $this->logger->addInfo('This is the start of a Drupal show site event: ' . $site->getUrl());

    if ($site->hasOlderCoreVersion() && $site->getIsSecurityCoreVersion()) {
      $event->addTemplate('DeesonWardenBundle:Drupal:securityUpdateRequired.html.twig');
    }

    $event->addTemplate('DeesonWardenBundle:Drupal:siteDetails.html.twig');
    $event->addParam('coreVersion', $site->getCoreVersion());
    $event->addParam('latestCoreVersion', $site->getLatestCoreVersion());

    $modulesRequiringUpdates = $site->getModulesRequiringUpdates();
    if (!empty($modulesRequiringUpdates)) {
      $event->addTemplate('DeesonWardenBundle:Drupal:moduleUpdates.html.twig');
      $event->addParam('modulesRequiringUpdates', $modulesRequiringUpdates);
    }

    $event->addTemplate('DeesonWardenBundle:Drupal:modules.html.twig');
    $event->addParam('modules', $site->getModules());

    $this->logger->addInfo('This is the end of a Drupal show site event: ' . $site->getUrl());
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
