<?php

namespace Deeson\SiteStatusBundle\Controller;

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
    $manager = $this->get('module_manager');
    $modules = $manager->getDocumentsBy(array('isNew' => FALSE), array('projectName' => 'asc'));

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