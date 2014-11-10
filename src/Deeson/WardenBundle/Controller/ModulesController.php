<?php

namespace Deeson\WardenBundle\Controller;

use Deeson\WardenBundle\Managers\SiteManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Deeson\WardenBundle\Managers\ModuleManager;

class ModulesController extends Controller {

  /**
   * Default action for listing the modules available.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function IndexAction() {
    /** @var ModuleManager $manager */
    $moduleManager = $this->get('module_manager');
    /** @var SiteManager $siteManager */
    $siteManager = $this->get('site_manager');

    $sites = $siteManager->getAllDocuments();
    $sitesTotalCount = (is_array($sites)) ? 0 : $sites->count();
    $modules = $moduleManager->getDocumentsBy(array('isNew' => FALSE), array('projectName' => 'asc'));

    $moduleList = array();
    foreach ($modules as $module) {
      $module->setUsagePercentage($sitesTotalCount);
      $moduleList[$module->getSiteCount()][] = $module;
    }
    krsort($moduleList);

    $modules = array();
    foreach ($moduleList as $count) {
      foreach ($count as $module) {
        $modules[] = $module;
      }
    }

    $params = array(
      'modules' => $modules,
    );

    return $this->render('DeesonWardenBundle:Modules:index.html.twig', $params);
  }

  /**
   * Show the detail of the specific module
   *
   * @param string $projectName
   *   The projectName of the site to view
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function ShowAction($projectName) {
    /** @var ModuleManager $manager */
    $manager = $this->get('module_manager');
    $module = $manager->getDocumentBy(array('projectName' => $projectName));

    $params = array(
      'module' => $module,
    );

    return $this->render('DeesonWardenBundle:Modules:show.html.twig', $params);
  }

}