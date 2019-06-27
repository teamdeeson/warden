<?php

namespace Deeson\WardenThirdPartyLibraryBundle\Managers;

use Deeson\WardenThirdPartyLibraryBundle\Document\ThirdPartyLibraryDocument;
use Monolog\Logger;

class ThirdPartyLibraryManager extends ThirdPartyBaseManager {

  public function __construct($doctrine, Logger $logger) {
    parent::__construct($doctrine, $logger);
  }

  /**
   * @return string
   *   The type of this manager.
   *   e.g. 'LibraryDocument'
   */
  public function getType() {
    return 'ThirdPartyLibraryDocument';
  }

  /**
   * Create a new empty type of the object.
   *
   * @return ThirdPartyLibraryDocument
   */
  public function makeNewItem() {
    return new ThirdPartyLibraryDocument();
  }

  /**
   * @param $name
   * @param $type
   *
   * @return mixed|null
   */
  public function getLibrary($name, $type) {
    $result = $this->getRepository()->findBy(array('type' => $type, 'name' => $name));
    if (count($result) < 0) {
      return NULL;
    }

    return array_shift($result);
  }
}
