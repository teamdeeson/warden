<?php

namespace Deeson\WardenBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

class InstallCommand extends ContainerAwareCommand {

  protected function configure() {
    $this->setName('deeson:warden:install')
      ->setDescription('Installer for configuring the application for the first time.')
      ->addOption('regenerate', NULL, InputOption::VALUE_NONE, 'Set this to regenerate the site config file.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $configFile = $this->getContainer()->getParameter('site_config_file');
    if (file_exists($configFile) && !$input->getOption('regenerate')) {
      $output->writeln('Config file already exists.');
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

    $this->generateConfigFiles($configFile, $username, $password);
    $output->writeln('Generated config file.');
  }

  /**
   * Generate the config files for the application.
   *
   * @param string $configFile
   * @param string $username
   * @param string $password
   */
  protected function generateConfigFiles($configFile, $username, $password) {
    $configData = array(
      'users' => array(
        $username => array(
          'pass' => hash('sha512', $password),
          'roles' => array(
            'ROLE_USER'
          )
        )
      )
    );
    $siteConfig = Yaml::dump($configData);
    file_put_contents($configFile, $siteConfig);

    // Create custom css file
    $appRoot = $this->getContainer()->get('kernel')->getRootDir();
    $customCssFile = $appRoot . '/../src/Deeson/WardenBundle/Resources/public/css/warden-custom.css';

    if (!file_exists($customCssFile)) {
      file_put_contents($customCssFile, '');
    }
  }

}