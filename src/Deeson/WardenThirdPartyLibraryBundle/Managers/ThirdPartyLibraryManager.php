<?php

namespace Deeson\WardenThirdPartyLibraryBundle\Managers;

use Deeson\WardenBundle\Document\SiteDocument;
use Deeson\WardenBundle\Event\SiteShowEvent;
use Deeson\WardenBundle\Event\SiteUpdateEvent;
use Deeson\WardenBundle\Managers\BaseManager;
use Deeson\WardenBundle\Managers\SiteManager;
use Deeson\WardenThirdPartyLibraryBundle\Document\ThirdPartyLibraryDocument;
use Monolog\Logger;

class ThirdPartyLibraryManager extends BaseManager {

  /**
   * @var SiteManager
   */
  protected $siteManager;

  public function __construct($doctrine, Logger $logger, SiteManager $siteManager) {
    parent::__construct($doctrine, $logger);
    $this->siteManager = $siteManager;
  }

  /**
   * @return string
   *   The type of this manager.
   *   e.g. 'LibraryDocument'
   */
  public function getType() {
    return 'ThirdPartyLibraryDocument';
  }

  /**
   * Create a new empty type of the object.
   *
   * @return ThirdPartyLibraryDocument
   */
  public function makeNewItem() {
    return new ThirdPartyLibraryDocument();
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
    $site = $event->getSite();

    // List the third party libraries that are used on the site.
    $libraries = $site->getLibraries();
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
    $site = $event->getSite();
    $libraryData = array();
    if (isset($data->library)) {
      $library = json_decode(json_encode($data->library), TRUE);
      $libraryData = (is_array($library)) ? $library : NULL;
    }
    $site->setLibraries($libraryData);
  }

  /**
   * Build the list of third party library details from the sites.
   */
  public function buildList() {
    $sites = $this->siteManager->getAllDocuments();

    foreach ($sites as $site) {
      /** @var SiteDocument $site */
      $this->addSiteLibraries($site);
    }
  }

  /**
   * The Mongodb repository name.
   *
   * @return string
   */
  protected function getRepositoryName() {
    return 'DeesonWardenThirdPartyLibraryBundle:' . $this->getType();
  }

  /**
   * @param $name
   * @param $type
   *
   * @return mixed|null
   */
  protected function getLibrary($name, $type) {
    $result = $this->getRepository()->findBy(array('type' => $type, 'name' => $name));
    if (count($result) < 0) {
      return NULL;
    }

    return array_shift($result);
  }

  /**
   * Adds the site library details to the third party libraries list.
   *
   * @param SiteDocument $site
   */
  protected function addSiteLibraries(SiteDocument $site) {
    $libraries = $site->getLibraries();
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
      foreach ($list as $item) {
        /** @var ThirdPartyLibraryDocument $thirdPartyLibrary */
        $thirdPartyLibrary = $this->getLibrary($item['name'], $type);
        if (empty($thirdPartyLibrary)) {
          $thirdPartyLibrary = $this->makeNewItem();
          $thirdPartyLibrary->setName($item['name']);
          $thirdPartyLibrary->setType($type);
        }

        $thirdPartyLibrary->addSite($site->getId(), $site->getName(), $item['version']);
        $this->saveDocument($thirdPartyLibrary);
      }
    }
  }

  /**
   * Add check for old format of data.
   *
   * @param array $data
   *
   * @return bool
   *
   * @deprecated as of version 1.2.0
   */
  protected function isDataInOldFormat($data) {
    return (!isset($data[0]['name']));
  }
}
