<?php

namespace Deeson\WardenThirdPartyLibraryBundle\Services;

use Deeson\WardenBundle\Document\SiteDocument;
use Deeson\WardenBundle\Event\SiteShowEvent;
use Deeson\WardenBundle\Event\SiteUpdateEvent;
use Deeson\WardenBundle\Managers\SiteManager;
use Deeson\WardenThirdPartyLibraryBundle\Document\ThirdPartyLibraryDocument;
use Deeson\WardenThirdPartyLibraryBundle\Document\SiteThirdPartyLibraryDocument;
use Deeson\WardenThirdPartyLibraryBundle\Managers\ThirdPartyLibraryManager;
use Deeson\WardenThirdPartyLibraryBundle\Managers\SiteThirdPartyLibraryManager;
use Monolog\Logger;

class ThirdPartyLibraryService {

  /**
   * @var Logger
   */
  protected $logger;

  /**
   * @var SiteManager
   */
  protected $siteManager;

  /**
   * @var ThirdPartyLibraryManager
   */
  protected $thirdPartyManager;

  /**
   * @var SiteThirdPartyLibraryManager
   */
  protected $siteThirdPartyManager;

  public function __construct($doctrine, Logger $logger, SiteManager $siteManager, ThirdPartyLibraryManager $thirdPartyManager, SiteThirdPartyLibraryManager $siteThirdPartyManager) {
    $this->logger = $logger;
    $this->siteManager = $siteManager;
    $this->thirdPartyManager = $thirdPartyManager;
    $this->siteThirdPartyManager = $siteThirdPartyManager;
  }

  /**
   * Event: warden.cron
   *
   * Fired when cron is run to update the third party libraries list.
   */
  public function onWardenCron() {
    $this->buildList();
  }

  /**
   * Event: warden.site.show
   *
   * @param SiteShowEvent $event
   */
  public function onWardenSiteShow(SiteShowEvent $event) {
    /** @var SiteDocument $site */
    $site = $event->getSite();
    /** @var SiteThirdPartyLibraryDocument $siteLibrary */
    $siteLibrary = $this->siteThirdPartyManager->findBySiteId($site->getId());
    if (empty($siteLibrary)) {
      return;
    }

    // List the third party libraries that are used on the site.
    $libraries = $siteLibrary->getLibraries();
    if (!empty($libraries)) {
      foreach ($libraries as $type => $data) {
        if ($this->isDataInOldFormat($data)) {
          break;
        }
        $event->addTabTemplate($type, 'DeesonWardenThirdPartyLibraryBundle:Sites:libraries.html.twig', $data);
      }
    }
  }

  /**
   * Event: warden.site.update
   *
   * Update the site document with details about libraries.
   *
   * @param SiteUpdateEvent $event
   */
  public function onWardenSiteUpdate(SiteUpdateEvent $event) {
    $data = $event->getData();
    /** @var SiteDocument $site */
    $site = $event->getSite();
    $this->logger->addInfo("Updating libraries for: " . $site->getName());

    /** @var SiteThirdPartyLibraryDocument $siteLibrary */
    $siteLibrary = $this->siteThirdPartyManager->findBySiteId($site->getId());
    if (empty($siteLibrary)) {
      $siteLibrary = $this->siteThirdPartyManager->makeNewItem();
      $siteLibrary->setSiteId($site->getId());
    }

    $libraryData = array();
    if (isset($data->library) && is_object($data->library)) {
      $library = json_decode(json_encode($data->library), TRUE);
      $libraryData = (is_array($library)) ? $library : NULL;
    }
    $siteLibrary->setLibraries($libraryData);
    $this->siteThirdPartyManager->saveDocument($siteLibrary);
  }

  /**
   * Build the list of third party library details from the sites.
   */
  public function buildList() {
    $this->thirdPartyManager->deleteAll();
    $sites = $this->siteManager->getAllDocuments();

    foreach ($sites as $site) {
      /** @var SiteDocument $site */
      $this->addSiteLibraries($site);
    }
  }

  /**
   * Adds the site library details to the third party libraries list.
   *
   * @param SiteDocument $site
   */
  protected function addSiteLibraries(SiteDocument $site) {
    /** @var SiteThirdPartyLibraryDocument $siteLibrary */
    $siteLibrary = $this->siteThirdPartyManager->findBySiteId($site->getId());
    if (empty($siteLibrary)) {
      $this->logger->addInfo("There are no third party library data for: " . $site->getName());
      return;
    }

    $libraries = $siteLibrary->getLibraries();
    $this->logger->addInfo("Checking libraries for: " . $site->getName());
    if (empty($libraries)) {
      $this->logger->addInfo("There are no libraries available for: " . $site->getName());
      return;
    }

    $this->logger->addInfo("Updated libraries for: " . $site->getName());
    foreach ($libraries as $type => $list) {
      if ($this->isDataInOldFormat($list)) {
        break;
      }
      $this->updateThirdPartyData($site, $list, $type);
    }
  }

  /**
   * Updates the third party data with the sites library data.
   *
   * @param SiteDocument $site
   * @param array $list
   *   The list of library data.
   * @param string $type
   *   The library data type.
   */
  protected function updateThirdPartyData(SiteDocument $site, array $list, $type) {
    foreach ($list as $item) {
      /** @var ThirdPartyLibraryDocument $thirdPartyLibrary */
      $thirdPartyLibrary = $this->thirdPartyManager->getLibrary($item['name'], $type);
      if (empty($thirdPartyLibrary)) {
        $thirdPartyLibrary = $this->thirdPartyManager->makeNewItem();
        $thirdPartyLibrary->setName($item['name']);
        $thirdPartyLibrary->setType($type);
      }

      $thirdPartyLibrary->addSite($site, $item['version']);
      $this->thirdPartyManager->saveDocument($thirdPartyLibrary);
    }
  }

  /**
   * Add check for old format of data.
   *
   * @param array $data
   *
   * @return bool
   *
   * @deprecated as of version 2.0
   */
  protected function isDataInOldFormat($data) {
    return (!isset($data[0]['name']));
  }
}
