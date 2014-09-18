<?php

namespace Deeson\SiteStatusBundle\Controller;

use Deeson\SiteStatusBundle\Managers\SiteManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Deeson\SiteStatusBundle\Managers\ModuleManager;

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

    $sitesTotalCount = $siteManager->getAllDocumentCount();
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

    return $this->render('DeesonSiteStatusBundle:Modules:index.html.twig', $params);
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

    return $this->render('DeesonSiteStatusBundle:Modules:show.html.twig', $params);
  }

}