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

    // Rebuild the dashboard based upon active sites.
    $sites = $siteManager->getDocumentsBy(array('isNew' => FALSE));
    foreach ($sites as $site) {
      /** @var SiteDocument $site */

      $output->writeln('Checking site: ' . $site->getId() . ' - ' . $site->getUrl());

      $hasCriticalIssue = $site->hasCriticalIssues();
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
          $hasCriticalIssue = TRUE;
        }

        $modulesNeedUpdate[] = $siteModule;
      }

      if ($site->getLatestCoreVersion() == $site->getCoreVersion() && !$isModuleSecurityUpdate && !$hasCriticalIssue) {
        continue;
      }

      $output->writeln('Adding site to dashboard: ' . $site->getId() . ' - ' . $site->getUrl());

      /** @var DashboardDocument $dashboard */
      $dashboard = $dashboardManager->makeNewItem();
      $dashboard->setName($site->getName());
      $dashboard->setSiteId($site->getId());
      $dashboard->setUrl($site->getUrl());
      $dashboard->setCoreVersion($site->getCoreVersion(), $site->getLatestCoreVersion(), $site->getIsSecurityCoreVersion());
      $dashboard->setHasCriticalIssue($hasCriticalIssue);
      $dashboard->setAdditionalIssues($site->getAdditionalIssues());
      $dashboard->setModules($modulesNeedUpdate);

      $dashboardManager->saveDocument($dashboard);
    }
  }

}
