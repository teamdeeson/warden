<?php

namespace Deeson\WardenThirdPartyLibraryBundle\Controller;

use Deeson\WardenThirdPartyLibraryBundle\Document\ThirdPartyLibraryDocument;
use Deeson\WardenBundle\Managers\SiteManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Deeson\WardenThirdPartyLibraryBundle\Managers\ThirdPartyLibraryManager;

class ThirdPartyLibrariesController extends Controller {

  /**
   * Default action for listing the third party libraries available.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function IndexAction() {
    /** @var ThirdPartyLibraryManager $thirdPartyLibraryManager */
    $thirdPartyLibraryManager = $this->get('warden.third_party_library.library');

    /** @var SiteManager $siteManager */
    $siteManager = $this->get('warden.site_manager');

    $sitesTotalCount = $siteManager->getAllDocumentsCount();
    $libraries = $thirdPartyLibraryManager->getAllDocuments();

    $libraryList = array();
    foreach ($libraries as $library) {
      /** @var ThirdPartyLibraryDocument $library */
      $library->setUsagePercentage($sitesTotalCount);
      $libraryList[$library->getType()][$library->getSiteCount()][] = $library;
    }
    ksort($libraryList);

    $libraries = array();
    foreach ($libraryList as $type => $count) {
      krsort($libraryList[$type]);
      $result = array();
      array_walk_recursive($libraryList[$type], function($v) use (&$result) { $result[] = $v; });
      $libraries[$type] = $result;
    }

    $params = array(
      'libraryList' => $libraries,
    );

    return $this->render('DeesonWardenThirdPartyLibraryBundle:ThirdPartyLibraries:index.html.twig', $params);
  }

  /**
   * Show the detail of the specific third party library
   *
   * @param string $libraryName
   *   The library name to view
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function ShowAction($libraryName) {
    /** @var ThirdPartyLibraryManager $thirdPartyLibraryManager */
    $manager = $this->get('warden.third_party_library.library');
    /** @var ThirdPartyLibraryDocument $library */
    $library = $manager->getDocumentBy(array('urlSafeName' => $libraryName));

    $sites = array();
    foreach ($library->getSites() as $site) {
      $sites[$site['name']] = $site;
    }
    ksort($sites);

    $params = array(
      'library' => $library,
      'sites' => $sites,
    );

    return $this->render('DeesonWardenThirdPartyLibraryBundle:ThirdPartyLibraries:show.html.twig', $params);
  }

}
