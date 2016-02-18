<?php

/**
 * @file
 * This is used for post composer actions.
 */

namespace Deeson\WardenBundle\Composer;

use Composer\Script\Event;
use Deeson\WardenBundle\Services\UserProviderService;
use Deeson\WardenBundle\Services\WardenSetupService;

class ScriptHandler {

  /**
   * Setup Warden from Composer.
   *
   * @param Event $event
   * @throws \Exception
   */
  static function setupWarden(Event $event) {
    $output = $event->getIO();
    $rootDir = getcwd();

    $userProviderService = new UserProviderService($rootDir . '/app');
    $wardenSetupService = new WardenSetupService($rootDir . '/app');

    if (!$userProviderService->isSetup()) {
      $username = '';
      while (strlen($username) < 1) {
        $username = $output->ask('Please enter the admin username [admin]: ', 'admin');
      }

      for ($password = '', $retries = 0; strlen($password) < 8 && $retries < 5; $retries++) {
        $password = $output->askAndHideAnswer('Please enter the admin password (minimum of 8 characters): ', '');
      }
      if (empty($password)) {
        throw new \InvalidArgumentException('An admin password is required.');
      }

      $output->write(' - Setting up the password file ...');
      $userProviderService->generateLoginFile($username, $password);
      $output->write(' - Setting up the CSS file ...');
      $wardenSetupService->generateCSSFile();
    }

    $output->write('Warden installation complete.');
  }
}
