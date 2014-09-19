<?php

namespace Deeson\SiteStatusBundle\Command;

use Deeson\SiteStatusBundle\Document\NeedUpdateDocument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Deeson\SiteStatusBundle\Managers\SiteManager;
use Deeson\SiteStatusBundle\Managers\NeedUpdateManager;

class BuildNeedsUpdateCommand extends ContainerAwareCommand {

  protected function configure() {
    $this->setName('deeson:site-status:build-update-list')
      ->setDescription('Builds list of sites that need updating');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    /** @var SiteManager $siteManager */
    $siteManager = $this->getContainer()->get('site_manager');
    /** @var NeedUpdateManager $needUpdateManager */
    $needUpdateManager = $this->getContainer()->get('need_update_manager');

    // Remove all 'needupdate' documents.
    $needUpdateManager->removeAll();

    // Rebuild the new ones.
    //$sites = $siteManager->getAllDocuments();
    $sites = $siteManager->getDocumentsBy(array('isNew' => FALSE));
    foreach ($sites as $site) {
      /** @var \Deeson\SiteStatusBundle\Document\SiteDocument $site */
      if ($site->getLatestCoreVersion() == $site->getCoreVersion()) {
        continue;
      }

      $output->writeln('Adding site: ' . $site->getId() . ' - ' . $site->getUrl());
      //print "\t{$site->getLatestCoreVersion()} == {$site->getCoreVersion()}\n";

      /** @var NeedUpdateDocument $needUpdate */
      $needUpdate = $needUpdateManager->makeNewItem();
      $needUpdate->setSiteId($site->getId());
      $needUpdate->setUrl($site->getUrl());
      $needUpdate->setCoreVersion($site->getCoreVersion(), $site->getLatestCoreVersion(), $site->getIsSecurityCoreVersion());

      $modulesNeedUpdate = array();
      foreach ($site->getModules() as $siteModule) {
        if (!isset($siteModule['latestVersion'])) {
          continue;
        }
        if ($siteModule['version'] == $siteModule['latestVersion']) {
          continue;
        }
        $modulesNeedUpdate[] = $siteModule;
      }

      $needUpdate->setModules($modulesNeedUpdate);
      $needUpdateManager->saveDocument($needUpdate);
    }
  }

}