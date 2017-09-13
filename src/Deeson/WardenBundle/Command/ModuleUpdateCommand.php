<?php

namespace Deeson\WardenBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Deeson\WardenBundle\Managers\SiteManager;
use Deeson\WardenBundle\Services\DrupalModuleService;

class ModuleUpdateCommand extends ContainerAwareCommand {

  protected function configure() {
    $this->setName('deeson:warden:update-modules')
      ->setDescription('Update the module details');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    /** @var DrupalModuleService $moduleManager */
    $moduleManager = $this->getContainer()->get('warden.drupal.module_service');
    $moduleManager->rebuildAllModuleSites();
  }

}
