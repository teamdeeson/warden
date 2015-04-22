<?php

namespace Deeson\WardenBundle\Command;

use Deeson\WardenBundle\Document\SiteDocument;
use Deeson\WardenBundle\Document\DashboardDocument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Deeson\WardenBundle\Managers\SiteManager;
use Deeson\WardenBundle\Managers\DashboardManager;

class BuildDashboardCommand extends ContainerAwareCommand {

  protected function configure() {
    $this->setName('deeson:warden:build-dashboard')
      ->setDescription('Builds list of sites that need to be displayed on the dashboard.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    /** @var SiteManager $siteManager */
    $siteManager = $this->getContainer()->get('warden.site_manager');

    /** @var DashboardManager $dashboardManager */
    $dashboardManager = $this->getContainer()->get('warden.dashboard_manager');

    // Remove all 'have_issue' documents.
    $dashboardManager->deleteAll();

    // Rebuild the new ones.
    $sites = $siteManager->getDocumentsBy(array('isNew' => FALSE));
    foreach ($sites as $site) {
      /** @var SiteDocument $site */
      // @todo site could be right core version, but module might have security issue.

      $output->writeln('Checking site: ' . $site->getId() . ' - ' . $site->getUrl());

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

      /** @var DashboardDocument $needUpdate */
      $needUpdate = $dashboardManager->makeNewItem();
      $needUpdate->setName($site->getName());
      $needUpdate->setSiteId($site->getId());
      $needUpdate->setUrl($site->getUrl());
      $needUpdate->setCoreVersion($site->getCoreVersion(), $site->getLatestCoreVersion(), $site->getIsSecurityCoreVersion());
      $needUpdate->setAdditionalIssues($site->getAdditionalIssues());
      $needUpdate->setModules($modulesNeedUpdate);

      $dashboardManager->saveDocument($needUpdate);
    }
  }

}