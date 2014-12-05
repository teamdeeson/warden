<?php

namespace Deeson\WardenBundle\Command;

use Deeson\WardenBundle\Document\ModuleDocument;
use Deeson\WardenBundle\Document\SiteDocument;
use Deeson\WardenBundle\Services\DrupalUpdateRequestService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Deeson\WardenBundle\Managers\SiteManager;
use Deeson\WardenBundle\Managers\ModuleManager;

class DrupalUpdateCommand extends ContainerAwareCommand {

  /**
   * @var DrupalUpdateRequestService
   */
  protected $drupalUpdateService;

  /**
   * @var array
   */
  protected $moduleVersions = array();

  /**
   * @var string
   */
  protected $projectStatus = '';

  protected function configure() {
    $this->setName('deeson:warden:drupal-update')
      ->setDescription('Update core & all the modules with the latest versions from Drupal.org')
      ->addOption('import-new', NULL, InputOption::VALUE_NONE, 'If set will only import data on newly created sites');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    /** @var DrupalUpdateRequestService drupalUpdateService */
    $this->drupalUpdateService = $this->getContainer()->get('drupal_update_service');

    /** @var SiteManager $siteManager */
    $siteManager = $this->getContainer()->get('site_manager');
    /** @var ModuleManager $moduleManager */
    $moduleManager = $this->getContainer()->get('module_manager');

    $updateNewSitesOnly = ($input->getOption('import-new'));

    // @todo refactor this!

    $moduleLatestVersion = array();
    $majorVersions = $siteManager->getAllMajorVersionReleases();

    foreach ($majorVersions as $version) {
      $modules = $moduleManager->getAllByVersion($version);
      foreach ($modules as $module) {
        /** @var ModuleDocument $module */
        $output->writeln('Updating - ' . $module->getProjectName() . ' for version: ' . $version);

        try {
          $this->processDrupalUpdateData($module->getProjectName(), $version);
        } catch (\Exception $e) {
          $output->writeln(' - Unable to update module version [' . $version . ']: ' . $e->getMessage());
          continue;
        }

        $moduleVersions = $this->moduleVersions;
        $moduleLatestVersion[$version][$module->getProjectName()] = $moduleVersions;

        $module->setName($this->drupalUpdateService->getModuleName());
        $module->setIsNew(FALSE);
        $module->setLatestVersion($version, $moduleVersions);
        $module->setProjectStatus($this->projectStatus);
        $moduleManager->updateDocument();
      }
    }

    $output->writeln("\n-----------\n");

    foreach ($majorVersions as $version) {
      // Update the core after the modules to update the versions of the modules
      // for a site.
      $output->writeln('Updating - Drupal version: ' . $version);

      try {
        $this->processDrupalUpdateData('drupal', $version);
      } catch (\Exception $e) {
        $output->writeln(' - Unable to update module version [' . $version . ']: ' . $e->getMessage());
      }

      $newOnly = ($updateNewSitesOnly) ? array('isNew' => TRUE) : array();
      $sites = $siteManager->getDocumentsBy(array_merge(array('coreVersion.release' => $version), $newOnly));

      // Update the sites for the major version with the latest core & module version information.
      $moduleVersions = $this->moduleVersions[ModuleDocument::MODULE_VERSION_TYPE_RECOMMENDED];
      foreach ($sites as $site) {
        /** @var SiteDocument $site */
        $output->writeln('Updating site: ' . $site->getId() . ' - ' . $site->getUrl());

        if ($site->getCoreReleaseVersion() != $version) {
          continue;
        }

        if (isset($moduleLatestVersion[$version])) {
          $site->setModulesLatestVersion($moduleLatestVersion[$version]);
        }

        $site->setLatestCoreVersion($moduleVersions['version'], $moduleVersions['isSecurity']);
        $siteManager->updateDocument();
      }
    }
  }

  /**
   * Gets the latest information on a module from Drupal.org.
   *
   * @param string $moduleName
   * @param int $version
   *
   * @throws \Exception
   */
  protected function processDrupalUpdateData($moduleName, $version) {
    try {
      $this->drupalUpdateService->setModuleRequestName($moduleName);
      $this->drupalUpdateService->setModuleRequestVersion($version);
      $this->drupalUpdateService->processRequest();

      $this->moduleVersions = $this->drupalUpdateService->getModuleVersions();
      $this->projectStatus = $this->drupalUpdateService->getProjectStatus();
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

}