<?php

namespace Deeson\WardenBundle\Command;

use Deeson\WardenBundle\Document\SiteDocument;
use Deeson\WardenBundle\Document\SiteHaveIssueDocument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Deeson\WardenBundle\Managers\SiteManager;
use Deeson\WardenBundle\Managers\SiteHaveIssueManager;

class BuildSiteHaveIssueCommand extends ContainerAwareCommand {

  protected function configure() {
    $this->setName('deeson:warden:build-sites-have-issues')
      ->setDescription('Builds list of sites that have issues reported.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    /** @var SiteManager $siteManager */
    $siteManager = $this->getContainer()->get('warden.site_manager');
    /** @var SiteHaveIssueManager $siteHaveIssueManager */
    $siteHaveIssueManager = $this->getContainer()->get('site_have_issue_manager');

    // Remove all 'have_issue' documents.
    $siteHaveIssueManager->deleteAll();

    // Rebuild the new ones.
    $sites = $siteManager->getDocumentsBy(array('isNew' => FALSE));
    foreach ($sites as $site) {
      /** @var SiteDocument $site */
      // @todo site could be right core version, but module might have security issue.

      $output->writeln('Checking site: ' . $site->getId() . ' - ' . $site->getUrl());
      //print "\t{$site->getLatestCoreVersion()} == {$site->getCoreVersion()}\n";

      $isModuleSecurityUpdate = FALSE;
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

        if ($siteModule['isSecurity']) {
          $isModuleSecurityUpdate = TRUE;
        }

        $modulesNeedUpdate[] = $siteModule;
      }

      if ($site->getLatestCoreVersion() == $site->getCoreVersion() && !$isModuleSecurityUpdate) {
        continue;
      }

      $output->writeln('Adding site to dashboard: ' . $site->getId() . ' - ' . $site->getUrl());

      /** @var SiteHaveIssueDocument $needUpdate */
      $needUpdate = $siteHaveIssueManager->makeNewItem();
      $needUpdate->setName($site->getName());
      $needUpdate->setSiteId($site->getId());
      $needUpdate->setUrl($site->getUrl());
      $needUpdate->setCoreVersion($site->getCoreVersion(), $site->getLatestCoreVersion(), $site->getIsSecurityCoreVersion());
      $needUpdate->setAdditionalIssues($site->getAdditionalIssues());
      $needUpdate->setModules($modulesNeedUpdate);

      $siteHaveIssueManager->saveDocument($needUpdate);
    }
  }

}