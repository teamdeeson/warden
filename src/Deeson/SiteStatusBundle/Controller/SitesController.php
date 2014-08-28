<?php

namespace Deeson\SiteStatusBundle\Controller;

use Deeson\SiteStatusBundle\Document\Site;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SitesController extends Controller
{
  /**
   * Default action for listing the sites available.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function IndexAction() {
    $params = array(
      'sites' => $this->getSitesList(),
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
  public function DetailAction($id) {
    $params = array(
      'site' => $this->getSiteData($id),
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

    $dm = $this->getDoctrineManager();
    $repository = $this->getDoctrineRepository($dm);
    $sitesByUrl = $repository->findBy(array('url' => $siteUrl));

    if ($sitesByUrl->count() < 1) {
      $site = new Site();
      $site->setUrl($siteUrl);
      $site->setSystemStatusToken($systemStatusToken);
      $site->setSystemStatusEncryptToken($systemStatusEncryptToken);

      $dm->persist($site);
      $dm->flush();

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
    $site = $this->getSiteData($id);

    $dm = $this->getDoctrineManager();
    $dm->remove($site);
    $dm->flush();

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
    $site = $this->getSiteData($id);

    /** @var StatusRequestService $statusService */
    $statusService = $this->get('site_status_service');
    //$statusService->setConnectionTimeout(10);
    $statusService->setSite($site);
    $statusService->requestSiteStatusData();

    $coreVersion = $statusService->getCoreVersion();
    //$moduleData = $statusRequest->getModuleData();
    $requestTime = $statusService->getRequestTime();

    $this->updateSite($id, array('coreVersion' => $coreVersion));

    $this->get('session')->getFlashBag()->add('notice', 'Your site has had the core version updated! (' . $requestTime . ' secs)');

    return $this->redirect('/sites/' . $id);
  }

  /**
   * Update the site details.
   *
   * @param $id
   * @param $siteData
   */
  protected function updateSite($id, $siteData) {
    $dm = $this->getDoctrineManager();
    $site = $this->getDoctrineRepository($dm)->find($id);

    foreach ($siteData as $key => $value) {
      $method = 'set' . ucfirst($key);
      if (!method_exists($site, $method)) {
        $this->get('session')->getFlashBag()->add('error', "Error: $method not valid on site object.");
        continue;
      }
      $site->$method($value);
    }

    $dm->flush();
  }

  /**
   * Get the list of sites.
   *
   * @return mixed
   */
  protected function getSitesList() {
    $repository = $this->getDoctrineRepository();

    return $repository->findAll();
  }

  /**
   * Get the specific site data.
   *
   * @param int $id
   *   The site id.
   *
   * @return \Deeson\SiteStatusBundle\Document\Site
   */
  protected function getSiteData($id) {
    $repository = $this->getDoctrineRepository();

    return $repository->find($id);
  }

  /**
   * Get the doctrine mongodb manager object.
   *
   * @return \Doctrine\ODM\MongoDB\DocumentManager
   */
  protected function getDoctrineManager() {
    return $this->get('doctrine_mongodb')->getManager();
  }

  /**
   * Get the Doctrine ObjectRepository for the respective collection.
   *
   * @param \Doctrine\ODM\MongoDB\DocumentManager $dm
   *
   * @return \Doctrine\Common\Persistence\ObjectRepository
   */
  protected function getDoctrineRepository($dm = NULL) {
    if (is_null($dm)) {
      $dm = $this->getDoctrineManager();
    }

    return $dm->getRepository('DeesonSiteStatusBundle:Site');
  }
}
