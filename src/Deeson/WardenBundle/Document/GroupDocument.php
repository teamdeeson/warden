<?php

namespace Deeson\WardenBundle\Document;

use FOS\UserBundle\Model\Group as BaseGroup;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(
 *     collection="user_group"
 * )
 */
class GroupDocument extends BaseGroup {

  /**
   * @MongoDB\Id(strategy="auto")
   */
  protected $id;

  public function __toString() {
    return $this->name;
  }
}
