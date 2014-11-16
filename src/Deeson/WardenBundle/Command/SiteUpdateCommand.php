<?php

namespace Deeson\WardenBundle\Command;

use Deeson\WardenBundle\Document\ModuleDocument;
use Deeson\WardenBundle\Document\SiteDocument;
use Deeson\WardenBundle\Services\WardenRequestService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Deeson\WardenBundle\Managers\SiteManager;
use Deeson\WardenBundle\Managers\ModuleManager;

class SiteUpdateCommand extends ContainerAwareCommand {

  protected function configure() {
    $this->setName('deeson:warden:update-sites')
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
      /** @var SiteDocument $site */
      $output->writeln('Updating site: ' . $site->getId() . ' - ' . $site->getUrl());

      /** @var WardenRequestService $statusService */
      try {
        $statusService = $this->getContainer()->get('site_status_service');
        //$statusService->setConnectionTimeout(10);
        if ($site->getAuthUser() && $site->getAuthPass()) {
          $headers = array(sprintf('Authorization: Basic %s', base64_encode($site->getAuthUser() . ':' . $site->getAuthPass())));
          $statusService->setConnectionHeaders($headers);
        }
        $statusService->setSite($site);
        $statusService->processRequest();
      /*} catch (SiteStatusRequestException $e) {
        $output->writeln('Request Error: ' . $e->getMessage());
        continue;*/
      } catch (\Exception $e) {
        $output->writeln('General Error - Unable to retrieve data from the site: ' . $e->getMessage());
        continue;
      }

      $coreVersion = $statusService->getCoreVersion();
      $moduleData = $statusService->getModuleData();
      ksort($moduleData);
      $requestTime = $statusService->getRequestTime();
      $additionalIssues = $statusService->getAdditionalIssues();

      foreach ($moduleData as $name => $version) {
        $majorVersion = ModuleDocument::getMajorVersion($version['version']);
        $moduleExists = $moduleManager->nameExists($name, $majorVersion);

        $moduleExistsCount = $moduleExists->count();
        /** @var ModuleDocument $module */
        $module = $moduleExists->getNext();

        $moduleLatestVersion = ($moduleExistsCount > 0) ? $module->getLatestVersion() : array();
        if ($moduleExistsCount > 0 && isset($moduleLatestVersion[$majorVersion])) {
          continue;
        }
        if ($moduleExistsCount < 1) {
          $module = $moduleManager->makeNewItem();
        }

        $module->setProjectName($name);
        $module->setLatestVersion($majorVersion);
        $moduleManager->saveDocument($module);
      }

      $output->writeln('request time: ' . $requestTime);

      //$site->setIsNew(FALSE);
      $site->setCoreVersion($coreVersion);
      $site->setModules($moduleData);
      $site->setAdditionalIssues($additionalIssues);
      $siteManager->updateDocument();

      $output->writeln('Update version: ' . $coreVersion);
    }
  }

}