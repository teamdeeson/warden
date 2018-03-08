<?php

namespace Deeson\WardenBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Deeson\WardenBundle\Managers\DashboardManager;

class DashboardSendNotificationCommand extends ContainerAwareCommand {

  protected function configure() {
    $this->setName('deeson:warden:dashboard-send-notification')
      ->setDescription('Sends a notification (email or Slack) of the sites which are listed on the dashboard.')
      ->setAliases(array('deeson:warden:dashboard-email-alert'))
      ->addOption('type', NULL, InputOption::VALUE_REQUIRED, 'Sets the type of notification to send. The value should either be \'email\' or \'slack\'');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $notificationType = $input->getOption('type');

    if (empty($notificationType)) {
      $output->writeln('You need to specify the type of notification that you would like to send - either email (--type=email) or Slack (--type=slack).');
      return;
    }

    /** @var DashboardManager $dashboardManager */
    $dashboardManager = $this->getContainer()->get('warden.dashboard_manager');

    switch ($notificationType) {
      case 'email':
        $dashboardManager->sendEmailNotification();
        break;
      case 'slack':
        $dashboardManager->sendSlackNotification();
        break;
      default:
        $output->writeln('The notification type is not recognised. It should be either email or slack.');
        break;
    }
  }

}
