<?php

namespace Deeson\WardenBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Deeson\WardenBundle\Managers\DashboardManager;

class DashboardEmailAlertCommand extends ContainerAwareCommand {

  protected function configure() {
    $this->setName('deeson:warden:dashboard-email-alert')
      ->setDescription('Sends an email alert to the specificed email address of the sites which are listed on the dashboard.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    /** @var DashboardManager $dashboardManager */
    $dashboardManager = $this->getContainer()->get('warden.dashboard_manager');
    $dashboardManager->sendUpdateEmail();
  }

}
