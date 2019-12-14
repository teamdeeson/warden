<?php

namespace Deeson\WardenBundle\Document;

use FOS\UserBundle\Model\Group;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(
 *     collection="user_group"
 * )
 */
class GroupDocument extends Group {

  /**
   * @MongoDB\Id(strategy="auto")
   */
  protected $id;

  public function __toString() {
    return $this->name;
  }
}
