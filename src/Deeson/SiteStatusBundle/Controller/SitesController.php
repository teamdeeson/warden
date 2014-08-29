<?php

namespace Deeson\SiteStatusBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Deeson\SiteStatusBundle\Managers\SiteManager;

class SitesController extends Controller {
  /**
   * Default action for listing the sites available.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function IndexAction() {
    /** @var SiteManager $manager */
    $manager = $this->get('site_manager');
    $sites = $manager->getAllEntities();

    $params = array(
      'sites' => $sites,
    );

    return $this->render('DeesonSiteStatusBundle:Sites:index.html.twig', $params);
  }

  /**
   * Show the detail of the specific site
   *
   * @param int $id
   *   The id of the site to view
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function ShowAction($id) {
    /** @var SiteManager $manager */
    $manager = $this->get('site_manager');
    $site = $manager->getEntityById($id);

    $params = array(
      'site' => $site,
    );

    return $this->render('DeesonSiteStatusBundle:Sites:detail.html.twig', $params);
  }

  /**
   * Add a new site to the system.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function AddAction() {
    $request = Request::createFromGlobals();
    $querySiteUrl = $request->query->get('siteUrl');
    list($siteUrl, $systemStatusToken, $systemStatusEncryptToken) = explode('|', $querySiteUrl);

    /** @var SiteManager $manager */
    $manager = $this->get('site_manager');

    if (!$manager->siteExists($siteUrl)) {
      $site = $manager->makeNewItem();
      $site->setUrl($siteUrl);
      $site->setSystemStatusToken($systemStatusToken);
      $site->setSystemStatusEncryptToken($systemStatusEncryptToken);
      $manager->saveEntity($site);
      $this->get('session')->getFlashBag()->add('notice', 'Your site has now been registered.');
    }
    else {
      $this->get('session')->getFlashBag()->add('error', 'Your site is already registered!');
    }

    return $this->redirect('/sites');
  }

  /**
   * Delete the site.
   *
   * @param int $id
   *   The site id to delete.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function DeleteAction($id) {
    /** @var SiteManager $manager */
    $manager = $this->get('site_manager');
    $manager->deleteEntity($id);

    return $this->redirect('/sites');
  }

  /**
   * Updates the core version for this site.
   *
   * @param int $id
   *   The site id to update the core version for.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function UpdateCoreAction($id) {
    //$requestTime = 0;
    //$coreVersion = '';
    $manager = $this->get('site_manager');
    $site = $manager->getEntityById($id);

    /** @var StatusRequestService $statusService */
    $statusService = $this->get('site_status_service');
    //$statusService->setConnectionTimeout(10);
    $statusService->setSite($site);
    $statusService->requestSiteStatusData();

    $coreVersion = $statusService->getCoreVersion();
    //$moduleData = $statusRequest->getModuleData();
    $requestTime = $statusService->getRequestTime();

    /** @var SiteManager $manager */
    $manager = $this->get('site_manager');
    $manager->updateEntity($id, array('coreVersion' => $coreVersion));

    $this->get('session')->getFlashBag()->add('notice', 'Your site has had the core version updated! (' . $requestTime . ' secs)');

    return $this->redirect('/sites/' . $id);
  }
}
