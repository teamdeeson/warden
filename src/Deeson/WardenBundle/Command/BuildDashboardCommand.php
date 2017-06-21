<?php

namespace Deeson\WardenBundle\Command;

use Deeson\WardenBundle\Document\SiteDocument;
use Deeson\WardenBundle\Event\DashboardUpdateEvent;
use Deeson\WardenBundle\Event\WardenEvents;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Deeson\WardenBundle\Managers\SiteManager;
use Deeson\WardenBundle\Managers\DashboardManager;
use Symfony\Component\EventDispatcher\EventDispatcher;

class BuildDashboardCommand extends ContainerAwareCommand {

  protected function configure() {
    $this->setName('deeson:warden:build-dashboard')
      ->setDescription('Builds list of sites that need to be displayed on the dashboard.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    /** @var SiteManager $siteManager */
    $siteManager = $this->getContainer()->get('warden.site_manager');

    /** @var EventDispatcher $dispatcher */
    $dispatcher = $this->getContainer()->get('event_dispatcher');

    /** @var DashboardManager $dashboardManager */
    $dashboardManager = $this->getContainer()->get('warden.dashboard_manager');

    // Remove all 'have_issue' documents.
    $dashboardManager->deleteAll();

    // Rebuild the dashboard based upon active sites.
    $sites = $siteManager->getDocumentsBy(array('isNew' => FALSE));
    foreach ($sites as $site) {
      /** @var SiteDocument $site */
      $output->writeln('Checking site: ' . $site->getId() . ' - ' . $site->getUrl());

      $event = new DashboardUpdateEvent($site);
      $dispatcher->dispatch(WardenEvents::WARDEN_DASHBOARD_UPDATE, $event);
    }
  }

}
