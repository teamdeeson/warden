<?php

namespace Deeson\WardenDrupalBundle\Command;

use Deeson\WardenDrupalBundle\Services\DrupalUpdateRequestService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
    $this->setName('deeson:warden-drupal:update')
      ->setDescription('Update core & all the modules with the latest versions from Drupal.org')
      ->addOption('import-new', NULL, InputOption::VALUE_NONE, 'If set will only import data on newly created sites');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    /** @var DrupalUpdateRequestService drupalUpdateService */
    $this->drupalUpdateService = $this->getContainer()->get('warden.drupal.module_version');
    $updateNewSitesOnly = ($input->getOption('import-new'));
    $this->drupalUpdateService->updateAllDrupalModules($updateNewSitesOnly);
  }
}
