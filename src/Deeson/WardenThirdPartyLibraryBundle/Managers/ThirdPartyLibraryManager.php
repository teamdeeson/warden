<?php

namespace Deeson\WardenThirdPartyLibraryBundle\Managers;

use Deeson\WardenBundle\Document\SiteDocument;
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
    print __METHOD__ . "\n";
    $this->buildList();
  }

  /**
   * Build the list of third party library details from the sites.
   */
  public function buildList() {
    $sites = $this->siteManager->getAllDocuments();

    foreach ($sites as $site) {
      /** @var SiteDocument $site */
      $this->addSiteToLibrary($site);
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
   * @param SiteDocument $site
   */
  protected function addSiteToLibrary(SiteDocument $site) {
    $libraries = $site->getLibraries();
    $this->logger->addInfo("Checking libraries for: " . $site->getName());
    if (empty($libraries)) {
      return;
    }

    $this->logger->addInfo("Updated libraries for: " . $site->getName());
    foreach ($libraries as $type => $list) {
      foreach ($list as $name => $version) {
        /** @var ThirdPartyLibraryDocument $thirdPartyLibrary */
        $thirdPartyLibrary = $this->getLibrary($name, $type);
        if (empty($thirdPartyLibrary)) {
          $thirdPartyLibrary = $this->makeNewItem();
          $thirdPartyLibrary->setName($name);
          $thirdPartyLibrary->setType($type);
        }

        $thirdPartyLibrary->addSite($site->getId(), $site->getName(), $version);
        $this->saveDocument($thirdPartyLibrary);
      }
    }
  }
}
