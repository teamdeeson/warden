<?php

/**
 * @file
 * An event which fires when rendering the details of a site.
 */

namespace Deeson\WardenBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class SiteShowEvent extends SiteEvent {

  /**
   * @var array
   */
  protected $templates = array();

  /**
   * @var array
   */
  protected $params = array();

  /**
   * @return array
   */
  public function getTemplates() {
    return $this->templates;
  }

  /**
   * @return array
   */
  public function getParams() {
    return $this->params;
  }

  /**
   * Add a template to the tabs.
   *
   * @param string $template
   */
  public function addTemplate($template) {
    $this->templates[] = $template;
  }

  /**
   * Add a parameter which will be available in the template.
   *
   * @param string $key
   * @param mixed $value
   */
  public function addParam($key, $value) {
    $this->params[$key] = $value;
  }
}