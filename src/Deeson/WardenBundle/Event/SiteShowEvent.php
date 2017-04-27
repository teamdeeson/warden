<?php

/**
 * @file
 * An event which fires when rendering the details of a site.
 */

namespace Deeson\WardenBundle\Event;

class SiteShowEvent extends SiteEvent {

  /**
   * @var array
   */
  protected $templates = array();

  /**
   * @var array
   */
  protected $tabTemplates = array();

  /**
   * @var array
   */
  protected $params = array();

  /**
   * Add a template to the tabs.
   *
   * @param string $template
   */
  public function addTemplate($template) {
    $this->templates[] = $template;
  }

  /**
   * @return array
   */
  public function getTemplates() {
    return $this->templates;
  }

  /**
   * Add a template to show in tabs.
   *
   * @param string $name
   *   The name which will group the templates together and label the tab.
   *
   * @param string $template
   *   The template name to render
   */
  public function addTabTemplate($name, $template) {
    $this->tabTemplates[$name][] = $template;
  }

  /**
   * @return array
   */
  public function getTabTemplates() {
    return $this->tabTemplates;
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

  /**
   * @return array
   */
  public function getParams() {
    return $this->params;
  }
}
