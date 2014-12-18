<?php

namespace Deeson\WardenBundle\Command;

use Deeson\WardenBundle\Document\ModuleDocument;
use Deeson\WardenBundle\Document\SiteDocument;
use Deeson\WardenBundle\Exception\DocumentNotFoundException;
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

    if ($input->getOption('import-new')) {
      try {
        $sites = $siteManager->getDocumentsBy(array('isNew' => TRUE));
      } catch (DocumentNotFoundException $e) {
        $sites = array();
      }
    }
    else {
      $sites = $siteManager->getAllDocuments();
    }

    foreach ($sites as $site) {
      /** @var SiteDocument $site */
      $output->writeln('Updating site: ' . $site->getId() . ' - ' . $site->getUrl());

      try {
        /** @var WardenRequestService $statusService */
        $statusService = $this->getContainer()->get('warden_request_service');
        //$statusService->setConnectionTimeout(10);

        if ($site->getAuthUser() && $site->getAuthPass()) {
          $headers = array(sprintf('Authorization: Basic %s', base64_encode($site->getAuthUser() . ':' . $site->getAuthPass())));
          $statusService->setConnectionHeaders($headers);
        }

        $statusService->setSite($site);
        $statusService->processRequest();
      } catch (\Exception $e) {
        $output->writeln('General Error - Unable to retrieve data from the site: ' . $e->getMessage());
        continue;
      }
    }
  }

}