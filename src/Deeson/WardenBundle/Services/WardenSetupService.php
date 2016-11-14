<?php

/**
 * @file
 */

namespace Deeson\WardenBundle\Services;

class WardenSetupService {

  /**
   * @var string
   */
  protected $appDir = '';

  /**
   * @var string
   */
  protected $customCssFile = '';

  /**
   * @param string $appDir
   */
  public function __construct($appDir) {
    $this->appDir = $appDir;
    $this->customCssFile = $this->appDir . '/../src/Deeson/WardenBundle/Resources/public/css/warden-custom.css';
  }

  /**
   * Generate the config files for the application.
   */
  public function generateCSSFile() {
    if (!file_exists($this->customCssFile)) {
      // Create the CSS directory if it doesn't exist.
      if (!file_exists(dirname($this->customCssFile))) {
        mkdir(dirname($this->customCssFile));
      }
      file_put_contents($this->customCssFile, '');
    }
  }

}
