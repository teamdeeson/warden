<?php

namespace Deeson\SiteStatusBundle\Command;

use Deeson\SiteStatusBundle\Document\SiteHaveIssueDocument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Deeson\SiteStatusBundle\Managers\SiteManager;
use Deeson\SiteStatusBundle\Managers\SiteHaveIssueManager;

class BuildSiteHaveIssueCommand extends ContainerAwareCommand {

  protected function configure() {
    $this->setName('deeson:site-status:build-sites-have-issues')
      ->setDescription('Builds list of sites that have issues reported.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    /** @var SiteManager $siteManager */
    $siteManager = $this->getContainer()->get('site_manager');
    /** @var SiteHaveIssueManager $siteHaveIssueManager */
    $siteHaveIssueManager = $this->getContainer()->get('site_have_issue_manager');

    // Remove all 'needupdate' documents.
    $siteHaveIssueManager->deleteAll();

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

      /** @var SiteHaveIssueDocument $needUpdate */
      $needUpdate = $siteHaveIssueManager->makeNewItem();
      $needUpdate->setSiteId($site->getId());
      $needUpdate->setUrl($site->getUrl());
      $needUpdate->setCoreVersion($site->getCoreVersion(), $site->getLatestCoreVersion(), $site->getIsSecurityCoreVersion());
      $needUpdate->setAdditionalIssues($site->getAdditionalIssues());

      $modulesNeedUpdate = array();
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

        $modulesNeedUpdate[] = $siteModule;
      }

      $needUpdate->setModules($modulesNeedUpdate);
      $siteHaveIssueManager->saveDocument($needUpdate);
    }
  }

}