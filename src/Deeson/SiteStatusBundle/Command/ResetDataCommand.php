<?php

namespace Deeson\SiteStatusBundle\Command;

use Deeson\SiteStatusBundle\Document\ModuleDocument;
use Deeson\SiteStatusBundle\Document\Site;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Deeson\SiteStatusBundle\Managers\SiteManager;
use Deeson\SiteStatusBundle\Managers\ModuleManager;

class ResetDataCommand extends ContainerAwareCommand {

  protected function configure() {
    $this->setName('deeson:site-status:reset')
      ->setDescription('This resets the data about site modules and all modules used.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    /** @var SiteManager $siteManager */
    $siteManager = $this->getContainer()->get('site_manager');
    /** @var ModuleManager $moduleManager */
    $moduleManager = $this->getContainer()->get('module_manager');

    $sites = $siteManager->getAllDocuments();
    foreach ($sites as $site) {
      /** @var Site $site */
      $site->setCoreVersion('');
      $site->setLatestCoreVersion('');
      $site->setModules(array());
      $siteManager->updateDocument();
    }

    $modules = $moduleManager->getAllDocuments();
    foreach ($modules as $module) {
      /** @var ModuleDocument $module */
      $moduleManager->deleteDocument($module->getId());
    }

    $output->writeln('Cleared out all data.');
  }

}