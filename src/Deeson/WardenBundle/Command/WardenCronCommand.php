<?php

namespace Deeson\WardenBundle\Command;

use Deeson\WardenBundle\Document\SiteDocument;
use Deeson\WardenBundle\Event\SiteEvent;
use Deeson\WardenBundle\Event\WardenEvents;
use Deeson\WardenBundle\Exception\DocumentNotFoundException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Deeson\WardenBundle\Managers\SiteManager;
use Symfony\Component\EventDispatcher\EventDispatcher;

class WardenCronCommand extends ContainerAwareCommand {

  protected function configure() {
    $this->setName('deeson:warden:warden-cron')
      ->setDescription('Trigger a cron event and request all sites are updated')
      ->addOption('import-new', NULL, InputOption::VALUE_NONE, 'If set will only import data on newly created sites');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    /** @var SiteManager $siteManager */
    $siteManager = $this->getContainer()->get('warden.site_manager');

    /** @var EventDispatcher $dispatcher */
    $dispatcher = $this->getContainer()->get('event_dispatcher');

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

    if (!empty($sites)) {
      $dispatcher->dispatch(WardenEvents::WARDEN_CRON);
    }

    foreach ($sites as $site) {
      /** @var SiteDocument $site */
      $output->writeln('Updating site: ' . $site->getId() . ' - ' . $site->getUrl());

      $event = new SiteEvent($site);

      try {
        $dispatcher->dispatch(WardenEvents::WARDEN_SITE_REFRESH, $event);
      }
      catch (\Exception $e) {
        $output->writeln('General Error - Unable to retrieve data from the site: ' . $e->getMessage());
        continue;
      }
    }
  }

}