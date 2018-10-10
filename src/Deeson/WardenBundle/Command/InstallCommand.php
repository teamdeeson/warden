<?php

namespace Deeson\WardenBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Deeson\WardenBundle\Services\WardenSetupService;
use Deeson\WardenBundle\Security\WebserviceUserProvider;

class InstallCommand extends ContainerAwareCommand {

  protected function configure() {
    $this->setName('deeson:warden:install')
      ->setDescription('Installer for configuring the application for the first time.')
      ->addOption('regenerate', NULL, InputOption::VALUE_NONE, 'Set this to regenerate the site config file.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    /** @var WardenSetupService $wardenSetupService */
    $wardenSetupService = $this->getContainer()->get('warden_setup');

    /** @var WebserviceUserProvider $userProviderService */
    $userProviderService = $this->getContainer()->get('warden.user_provider');

    if ($userProviderService->isSetup() && !$input->getOption('regenerate')) {
      $output->writeln('Warden username and password is already setup - check the README file if you need to regenerate.');
      return;
    }

    $helper = $this->getHelper('question');

    $usernameQuestion = new Question('Please enter the admin username [admin]: ', 'admin');
    $username = $helper->ask($input, $output, $usernameQuestion);

    $passwordQuestion = new Question('Please enter the admin password (minimum of 8 characters): ', '');
    $passwordQuestion->setValidator(function ($value) {
      if (trim($value) == '') {
        throw new \Exception('The password can not be empty');
      }
      if (strlen($value) < 8) {
        throw new \Exception('Password provided is too short - must be minimum of 8 characters');
      }

      return $value;
    });

    $passwordQuestion->setMaxAttempts(3);
    $passwordQuestion->setHidden(TRUE);
    $passwordQuestion->setHiddenFallback(FALSE);

    $password = $helper->ask($input, $output, $passwordQuestion);

    $output->writeln(' - Setting up the password file ...');
    $userProviderService->generateLoginFile($username, $password);
    $output->writeln(' - Setting up the CSS file ...');
    $wardenSetupService->generateCSSFile();

    $output->writeln('Warden installation complete.');
  }

}
