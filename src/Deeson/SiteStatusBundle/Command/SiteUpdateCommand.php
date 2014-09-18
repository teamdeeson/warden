<?php

namespace Deeson\SiteStatusBundle\Command;

use Deeson\SiteStatusBundle\Document\Module;
use Deeson\SiteStatusBundle\Document\Site;
use Deeson\SiteStatusBundle\Services\StatusRequestService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Deeson\SiteStatusBundle\Managers\SiteManager;
use Deeson\SiteStatusBundle\Managers\ModuleManager;

class SiteUpdateCommand extends ContainerAwareCommand {

  protected function configure() {
    $this->setName('deeson:site-status:update-sites')
      ->setDescription('Update the site status details')
      //->addArgument()
      ->addOption('import-new', NULL, InputOption::VALUE_NONE, 'If set will only import data on newly created sites');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    /** @var SiteManager $siteManager */
    $siteManager = $this->getContainer()->get('site_manager');
    /** @var ModuleManager $moduleManager */
    $moduleManager = $this->getContainer()->get('module_manager');

    if ($input->getOption('import-new')) {
      $sites = $siteManager->getDocumentsBy(array('isNew' => TRUE));
    }
    else {
      $sites = $siteManager->getAllDocuments();
    }

    foreach ($sites as $site) {
      /** @var Site $site */
      $output->writeln('Updating site: ' . $site->getId() . ' - ' . $site->getUrl());

      /** @var StatusRequestService $statusService */
      $statusService = $this->getContainer()->get('site_status_service');
      //$statusService->setConnectionTimeout(10);
      $statusService->setSite($site);
      $statusService->processRequest();

      $coreVersion = $statusService->getCoreVersion();
      $moduleData = $statusService->getModuleData();
      ksort($moduleData);
      $requestTime = $statusService->getRequestTime();

      foreach ($moduleData as $name => $version) {
        $majorVersion = Module::getMajorVersion($version['version']);
        $moduleExists = $moduleManager->nameExists($name, $majorVersion);

        $moduleExistsCount = $moduleExists->count();
        /** @var Module $module */
        $module = $moduleExists->getNext();

        //printf('<pre>%s</pre>', print_r($module, true));
        //die();
        //print "\n- module: $name";
        //print "\n\tcount: " . $moduleExistsCount;
        //print "\n\tversion: $majorVersion";
        $moduleLatestVersion = ($moduleExistsCount > 0) ? $module->getLatestVersion() : array();
        //print "\n\tversion exists: " . (isset($moduleLatestVersion[$majorVersion]) ? 'Y' : 'N');
        //die();
        if ($moduleExistsCount > 0 && isset($moduleLatestVersion[$majorVersion])) {
          //print "\n\tSKIP THIS - have version!\n";
          continue;
        }
        if ($moduleExistsCount < 1) {
          //print "\n\tNO MODULE AT ALL - create new one\n";
          $module = $moduleManager->makeNewItem();
        }
        elseif ($moduleExistsCount > 0 && !isset($moduleLatestVersion[$majorVersion])) {
          //print "\n\tModule, but no version - update an existing one\n";
        }

        $module->setProjectName($name);
        $module->setLatestVersion($majorVersion);
        $moduleManager->saveDocument($module);
      }

      $output->writeln('request time: ' . $requestTime);

      $site->setIsNew(FALSE);
      $site->setCoreVersion($coreVersion);
      $site->setModules($moduleData);
      $siteManager->updateDocument();

      $output->writeln('Update version: ' . $coreVersion);
    }
  }

}