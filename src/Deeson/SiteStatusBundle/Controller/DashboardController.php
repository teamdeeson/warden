<?php

namespace Deeson\SiteStatusBundle\Controller;

use Deeson\SiteStatusBundle\Managers\SiteManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DashboardController extends Controller {

  public function indexAction() {
    /** @var SiteManager $manager */
    $manager = $this->get('site_manager');
    $qb = $manager->createIndexQuery();
    $qb->field('coreVersion.current')->notEqual('coreVersion.latestRelease');

    $cursor = $qb->getQuery()->execute();

    $sites = array();
    foreach ($cursor as $result) {
      $sites[] = $result;
    }

    //printf('<pre>%s</pre>', print_r($sites, true));
//die();

    $qb = $manager->createIndexQuery();
    $qb->field('coreVersion.current')->notEqual('coreVersion.latestRelease');

    $cursor = $qb->getQuery()->execute();

    $siteModules = array();
    foreach ($cursor as $result) {
      $modules = array();
      foreach ($result->getModules() as $module) {
        if (!isset($module['latestVersion']) || $module['version'] == $module['latestVersion']) {
          continue;
        }
        $modules[] = $module;
      }
      $siteModules[$result->getId()] = $modules;
    }
    //printf('<pre>%s</pre>', print_r($siteModules, true));
    //die();

    $params = array(
      'sites' => $sites,
      'siteModules' => $siteModules,
    );

    return $this->render('DeesonSiteStatusBundle:Dashboard:index.html.twig', $params);
  }

}
