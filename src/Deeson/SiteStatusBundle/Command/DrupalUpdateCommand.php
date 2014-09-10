<?php

namespace Deeson\SiteStatusBundle\Command;

use Deeson\SiteStatusBundle\Document\Module;
use Deeson\SiteStatusBundle\Document\Site;
use Deeson\SiteStatusBundle\Services\DrupalUpdateRequestService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Deeson\SiteStatusBundle\Managers\SiteManager;
use Deeson\SiteStatusBundle\Managers\ModuleManager;

class DrupalUpdateCommand extends ContainerAwareCommand {

  /**
   * @var DrupalUpdateRequestService
   */
  protected $drupalUpdateService;

  /**
   * @var string
   */
  protected $latestReleaseVersion;

  /**
   * @var bool
   */
  protected $isSecurityRelease = FALSE;

  protected function configure() {
    $this->setName('deeson:site-status:drupal-update')
      ->setDescription('Update core & all the modules with the latest versions from Drupal.org');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    /** @var DrupalUpdateRequestService drupalUpdateService */
    $this->drupalUpdateService = $this->getContainer()->get('drupal_update_service');

    /** @var SiteManager $siteManager */
    $siteManager = $this->getContainer()->get('site_manager');
    /** @var ModuleManager $moduleManager */
    $moduleManager = $this->getContainer()->get('module_manager');

    // loop over all modules
    // for each module get unique major versions (e.g. 6.x/ 7.x)
    // request data from d.o for module and version
    // update module with latest version
    // see if it is a security release and flag if so
    // @todo refactor this!

    $modules = $moduleManager->getAllDocuments();
    //$modules = array($moduleManager->getDocumentById('5409c58f942a380a970041e1'));
    $moduleLatestVersion = array();
    // @todo get distinct core version number(s)
    $majorVersions = array('6', '7');

    foreach ($modules as $module) {
      foreach ($majorVersions as $version) {
        /** @var Module $module */
        $output->writeln('Updating - ' . $module->getProjectName() . ' for version: ' . $version);

        try {
          $this->processDrupalUpdateData($module->getProjectName(), $version);
        } catch (\Exception $e) {
          $output->writeln(' - Unable to update module version [' . $version . ']: ' . $e->getMessage());
          continue;
        }

        $securityToken = ($this->isSecurityRelease ? 'Y' : 'N');
        $output->writeln("\tsecurity: $securityToken");

        $moduleLatestVersion[$module->getProjectName()][$version] = array(
          'version' => $this->latestReleaseVersion,
          'isSecurity' => ($this->isSecurityRelease ? 1 : 0),
        );

        $module->setName($this->drupalUpdateService->getModuleName());
        $module->setIsNew(TRUE);
        $module->setLatestVersion($version, $this->latestReleaseVersion);
        $moduleManager->updateDocument();
      }
      //print_r($moduleLatestVersion);
      //die();
    }

    foreach ($majorVersions as $version) {
      // Update the core after the modules to update the versions of the modules
      // for a site.

      $output->writeln('Updating - Drupal version: ' . $version);

      try {
        $this->processDrupalUpdateData('drupal', $version);
      } catch (\Exception $e) {
        $output->writeln(' - Unable to update module version [' . $version . ']: ' . $e->getMessage());
        return;
      }

      //print "version: {$this->latestReleaseVersion}\n";
      //printf("security: %s\n", ($this->isSecurityRelease ? 'Y' : 'N'));
      //die();

      $sites = $siteManager->getAllDocuments();

      foreach ($sites as $site) {
        /** @var Site $site */
        $output->writeln('Updating site: ' . $site->getId() . ' - ' . $site->getUrl());

        if (!isset($moduleLatestVersion[$version])) {
          $output->writeln('No module version for version: ' . $version);
          continue;
        }

        $site->setLatestCoreVersion($this->latestReleaseVersion);
        $site->setModulesLatestVersion($moduleLatestVersion[$version]);
        // @todo update the site modules with the latest version
        $siteManager->updateDocument();
        //die();
      }
    }

    //die();
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
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }

    $latestRelease = $this->drupalUpdateService->getModuleLatestRelease();
    $latestReleaseVersion = (string) $latestRelease->version;

    $isSecurityRelease = FALSE;
    if (isset($latestRelease->terms)) {
      foreach ($latestRelease->terms->term as $term) {
        //print "{$term->name} => {$term->value}\n";
        if (strtolower($term->value) == 'security update') {
          $isSecurityRelease = TRUE;
          break;
        }
      }
    }

    $this->latestReleaseVersion = $latestReleaseVersion;
    $this->isSecurityRelease = $isSecurityRelease;
  }

}