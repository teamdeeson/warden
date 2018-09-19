<?php

namespace Deeson\WardenBundle\Asset\VersionStrategy;

use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

class DateVersionStrategy implements VersionStrategyInterface {

  /**
   * @param string $path
   *
   * @return bool|int|string
   */
  public function getVersion($path) {
    return filemtime($path);
  }

  /**
   * @param string $path
   *
   * @return string
   */
  public function applyVersion($path) {
    return sprintf('%s?v=%s', $path, $this->getVersion($path));
  }
}
