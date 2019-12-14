<?php

namespace Deeson\WardenBundle\Document;

use FOS\UserBundle\Model\User;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(
 *     collection="user"
 * )
 */
class UserDocument extends User {

  const ROLE_ADMIN = 'ROLE_ADMIN';

  /**
   * @MongoDB\Id(strategy="auto")
   */
  protected $id;

  /**
   * @MongoDB\Field(type="date")
   */
  protected $createdDate;

  /**
   * @MongoDB\ReferenceMany(targetDocument="Deeson\WardenBundle\Document\GroupDocument")
   */
  protected $groups;

  public function __construct() {
    parent::__construct();
  }

  /**
   * @return mixed
   */
  public function getGroups() {
    return $this->groups;
  }

  /**
   * @param mixed $groups
   */
  public function setGroups($groups) {
    $this->groups = $groups;
  }

  /**
   * @return array
   */
  public function getGroupIds() {
    $groupIds = [];
    foreach ($this->getGroups() as $group) {
      $groupIds[] = $group->getId();
    }

    return $groupIds;
  }

  /**
   * @return \DateTime|null
   */
  public function getCreatedDate() {
    return $this->createdDate;
  }

  /**
   * @param \DateTime|null $createdDate
   */
  public function setCreatedDate($createdDate) {
    $this->createdDate = $createdDate;
  }

}
