<?php

namespace Deeson\WardenBundle\Command;

use Deeson\WardenBundle\Event\CronEvent;
use Deeson\WardenBundle\Event\WardenEvents;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
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
      $sites = $siteManager->getDocumentsBy(array('isNew' => TRUE));
    }
    else {
      $sites = $siteManager->getAllDocuments();
    }

    if (empty($sites)) {
      $output->writeln('No sites available.');
      return;
    }

    $event = new CronEvent($sites);
    $dispatcher->dispatch(WardenEvents::WARDEN_CRON, $event);
  }

}
