<?php
/**
 * Created by PhpStorm.
 * User: mikedavis
 * Date: 29/08/2014
 * Time: 12:23
 */

namespace Deeson\SiteStatusBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

class BaseDocument {

  /**
   * @Mongodb\Id
   */
  protected $id;

  /**
   * @return mixed
   */
  public function getId() {
    return $this->id;
  }

  /**
   * @param mixed $id
   */
  public function setId($id) {
    $this->id = $id;
  }

}