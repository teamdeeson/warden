<?php

namespace Deeson\SiteStatusBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Deeson\SiteStatusBundle\Managers\SiteManager;
use Deeson\SiteStatusBundle\Services\StatusRequestService;
use Deeson\SiteStatusBundle\Document\SiteDocument;

class SitesController extends Controller {

  /**
   * Default action for listing the sites available.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function IndexAction() {
    /** @var SiteManager $manager */
    $manager = $this->get('site_manager');
    $sites = $manager->getDocumentsBy(array(), array('url' => 'asc'));

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
    $site = $manager->getDocumentById($id);

    $params = array(
      'site' => $site,
    );

    return $this->render('DeesonSiteStatusBundle:Sites:show.html.twig', $params);
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

    if (!$manager->urlExists($siteUrl)) {
      $site = $manager->makeNewItem();
      $site->setUrl($siteUrl);
      $site->setSystemStatusToken($systemStatusToken);
      $site->setSystemStatusEncryptToken($systemStatusEncryptToken);
      $manager->saveDocument($site);
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
    $manager->deleteDocument($id);

    return $this->redirect('/sites');
  }

  /**
   * Updates the core & module versions for this site.
   *
   * @param int $id
   *   The site id to update the core version for.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function RefreshAction($id) {
    /** @var SiteManager $manager */
    $manager = $this->get('site_manager');
    /** @var SiteDocument $site */
    $site = $manager->getDocumentById($id);

    /** @var StatusRequestService $statusService */
    $statusService = $this->get('site_status_service');
    //$statusService->setConnectionTimeout(10);
    $statusService->setSite($site);
    $statusService->processRequest();

    $coreVersion = $statusService->getCoreVersion();
    $moduleData = $statusService->getModuleData();
    ksort($moduleData);
    $requestTime = $statusService->getRequestTime();

    /** @var SiteManager $manager */
    $manager = $this->get('site_manager');
    $site->setIsNew(FALSE);
    $site->setCoreVersion($coreVersion);
    $site->setModules($moduleData);
    $manager->updateDocument();

    $this->get('session')->getFlashBag()->add('notice', 'Your site has had the core version updated! (' . $requestTime . ' secs)');

    return $this->redirect('/sites/' . $id . '/edit');
  }

  public function EditAction($id) {
    /** @var SiteManager $manager */
    $manager = $this->get('site_manager');
    $site = $manager->getDocumentById($id);

    $params = array(
      'site' => $site,
    );

    return $this->render('DeesonSiteStatusBundle:Sites:edit.html.twig', $params);
  }
}
