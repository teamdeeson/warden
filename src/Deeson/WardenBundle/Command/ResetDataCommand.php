<?php

namespace Deeson\WardenBundle\Command;

use Deeson\WardenBundle\Document\SiteDocument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Deeson\WardenBundle\Managers\SiteManager;
use Deeson\WardenBundle\Managers\ModuleManager;

class ResetDataCommand extends ContainerAwareCommand {

  protected function configure() {
    $this->setName('deeson:warden:reset')
      ->setDescription('This resets the data about site modules and all modules used.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    /** @var SiteManager $siteManager */
    $siteManager = $this->getContainer()->get('warden.site_manager');
    /** @var ModuleManager $moduleManager */
    $moduleManager = $this->getContainer()->get('warden.drupal.module_manager');

    $sites = $siteManager->getAllDocuments();
    foreach ($sites as $site) {
      /** @var SiteDocument $site */
      $site->setCoreVersion('');
      $site->setLatestCoreVersion('');
      $site->setModules(array());
      $siteManager->updateDocument();
    }

    // @todo trigger WARDEN_RESET_DATA event.
    $moduleManager->deleteAll();

    $output->writeln('Cleared out all data.');
  }

}
