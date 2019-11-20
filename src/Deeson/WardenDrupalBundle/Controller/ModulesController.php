<?php

namespace Deeson\WardenDrupalBundle\Controller;

use Deeson\WardenDrupalBundle\Document\DrupalModuleDocument;
use Deeson\WardenBundle\Document\SiteDocument;
use Deeson\WardenBundle\Managers\SiteManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Deeson\WardenDrupalBundle\Managers\DrupalModuleManager;
use Deeson\WardenDrupalBundle\Document\SiteDrupalModuleDocument;
use Deeson\WardenDrupalBundle\Managers\SiteDrupalModuleManager;

class ModulesController extends Controller {

  /**
   * Default action for listing the modules available.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function IndexAction() {
    /** @var DrupalModuleManager $manager */
    $moduleManager = $this->get('warden.drupal.module_manager');

    /** @var SiteManager $siteManager */
    $siteManager = $this->get('warden.site_manager');

    $sitesTotalCount = $siteManager->getAllDocumentsCount();
    $modules = $moduleManager->getDocumentsBy(array('isNew' => FALSE), array('projectName' => 'asc'));

    $moduleList = array();
    foreach ($modules as $module) {
      /** @var DrupalModuleDocument $module */
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

    return $this->render('DeesonWardenDrupalBundle:Modules:index.html.twig', $params);
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
    /** @var DrupalModuleManager $manager */
    $manager = $this->get('warden.drupal.module_manager');
    $module = $manager->getDocumentBy(array('projectName' => $projectName));

    /** @var SiteManager $manager */
    $manager = $this->get('warden.site_manager');
    $sites = $manager->getDocumentsBy(array(), array('name' => 'asc'));

    /** @var SiteDrupalModuleManager $siteModuleManager */
    $siteModuleManager = $this->get('warden.drupal.site_module_manager');

    $sitesNotUsingModule = array();
    foreach ($sites as $site) {
      /** @var SiteDocument $site */
      /** @var SiteDrupalModuleDocument $siteModuleDoc */
      $siteModuleDoc = $siteModuleManager->findBySiteId($site->getId());
      if (empty($siteModuleDoc)) {
        continue;
      }

      $usingModule = FALSE;
      foreach ($siteModuleDoc->getModules() as $siteModule) {
        if ($siteModule['name'] == $module->getProjectName()) {
          $usingModule = TRUE;
          break;
        }
      }
      if (!$usingModule) {
        $sitesNotUsingModule[$site->getName()] = $site;
      }
    }

    $sitesUsingModule = array();
    foreach ($module->getSites() as $moduleSite) {
      $sitesUsingModule[$moduleSite['name']] = $moduleSite;
    }
    ksort($sitesUsingModule);

    $params = array(
      'module' => $module,
      'sitesUsingModule' => $sitesUsingModule,
      'sitesNotUsingModule' => $sitesNotUsingModule,
    );

    return $this->render('DeesonWardenDrupalBundle:Modules:show.html.twig', $params);
  }

}
