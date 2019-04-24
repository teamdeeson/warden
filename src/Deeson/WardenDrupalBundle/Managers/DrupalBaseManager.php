<?php

namespace Deeson\WardenDrupalBundle\Managers;

use Deeson\WardenBundle\Managers\BaseManager;

abstract class DrupalBaseManager extends BaseManager {

  /**
   * The Mongodb repository name.
   *
   * @return string
   */
  protected function getRepositoryName() {
    return 'DeesonWardenDrupalBundle:' . $this->getType();
  }

}
