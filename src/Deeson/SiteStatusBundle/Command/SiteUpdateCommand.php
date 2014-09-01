<?php

namespace Deeson\SiteStatusBundle\Command;

use Deeson\SiteStatusBundle\Document\Module;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Deeson\SiteStatusBundle\Managers\SiteManager;
use Deeson\SiteStatusBundle\Managers\ModuleManager;

class SiteUpdateCommand extends ContainerAwareCommand {

  protected function configure() {
    $this->setName('deeson:site-status:update')
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
      $sites = $siteManager->getEntitiesBy(array('isNew' => TRUE));
    }
    else {
      $sites = $siteManager->getAllEntities();
    }

    foreach ($sites as $site) {
      /** @var \Deeson\SiteStatusBundle\Document\Site $site */
      $output->writeln('Updating site: ' . $site->getId() . ' - ' . $site->getUrl());

      /** @var StatusRequestService $statusService */
      $statusService = $this->getContainer()->get('site_status_service');
      //$statusService->setConnectionTimeout(10);
      $statusService->setSite($site);
      $statusService->requestSiteStatusData();

      $coreVersion = $statusService->getCoreVersion();
      $moduleData = $statusService->getModuleData();
      ksort($moduleData);
      $requestTime = $statusService->getRequestTime();

      //$output->writeln('modules: ' . print_r($moduleData, TRUE));

      foreach ($moduleData as $name => $version) {
        $moduleExists = $moduleManager->nameExists($name);

        if ($moduleExists) {
          continue;
        }

        /** @var \Deeson\SiteStatusBundle\Document\Module $module */
        $module = $moduleManager->makeNewItem();
        $module->setName($name);
        $moduleManager->saveEntity($module);
      }

      $output->writeln('request time: ' . $requestTime);

      $siteData = array(
        'isNew' => FALSE,
        'coreVersion' => $coreVersion,
        'modules' => $moduleData,
      );
      $siteManager->updateEntity($site->getId(), $siteData);

      $output->writeln('Update version: ' . $coreVersion);
    }
  }

}