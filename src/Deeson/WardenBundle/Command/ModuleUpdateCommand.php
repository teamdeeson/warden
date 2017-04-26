<?php

namespace Deeson\WardenBundle\Command;

use Deeson\WardenBundle\Document\SiteDocument;
use Deeson\WardenBundle\Exception\DocumentNotFoundException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Deeson\WardenBundle\Managers\SiteManager;
use Deeson\WardenBundle\Managers\ModuleManager;

class ModuleUpdateCommand extends ContainerAwareCommand {

  protected function configure() {
    $this->setName('deeson:warden:update-modules')
      ->setDescription('Update the module details')
      ->addOption('import-new', NULL, InputOption::VALUE_NONE, 'If set will only import data on newly created modules');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    /** @var SiteManager $siteManager */
    $siteManager = $this->getContainer()->get('warden.site_manager');
    /** @var ModuleManager $moduleManager */
    $moduleManager = $this->getContainer()->get('warden.drupal.module');

    if ($input->getOption('import-new')) {
      $sites = $siteManager->getDocumentsBy(array('isNew' => TRUE));
    }
    else {
      $sites = $siteManager->getAllDocuments();
    }

    foreach ($sites as $site) {
      /** @var SiteDocument $site */
      $output->writeln('Updating site: ' . $site->getId() . ' - ' . $site->getUrl());

      try {
        $site->updateModules($moduleManager);
      }
      catch (DocumentNotFoundException $e) {
        $output->writeln($e->getMessage());
      }
    }
  }

}
