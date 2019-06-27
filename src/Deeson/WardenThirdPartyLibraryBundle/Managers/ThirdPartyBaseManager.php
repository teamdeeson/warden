<?php

namespace Deeson\WardenThirdPartyLibraryBundle\Managers;

use Deeson\WardenBundle\Managers\BaseManager;

abstract class ThirdPartyBaseManager extends BaseManager {

  /**
   * The Mongodb repository name.
   *
   * @return string
   */
  protected function getRepositoryName() {
    return 'DeesonWardenThirdPartyLibraryBundle:' . $this->getType();
  }

}
