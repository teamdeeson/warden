<?php

namespace Deeson\WardenBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Deeson\WardenBundle\Managers\SiteManager;
use Deeson\WardenBundle\Services\WardenRequestService;
use Deeson\WardenBundle\Document\SiteDocument;

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

    return $this->render('DeesonWardenBundle:Sites:index.html.twig', $params);
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
    $modulesRequiringUpdates = $site->getModulesRequiringUpdates();

    $params = array(
      'site' => $site,
      'modulesRequiringUpdates' => $modulesRequiringUpdates,
    );

    return $this->render('DeesonWardenBundle:Sites:show.html.twig', $params);
  }

  /**
   * Add a new site to the system.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function AddAction() {
    $request = Request::createFromGlobals();
    $querySiteUrl = $request->query->get('siteUrl');
    list($siteUrl, $wardenToken, $wardenEncryptToken) = explode('|', $querySiteUrl);

    /** @var SiteManager $manager */
    $manager = $this->get('site_manager');

    if (!$manager->urlExists($siteUrl)) {
      $site = $manager->makeNewItem();
      $site->setUrl($siteUrl);
      $site->setWardenToken($wardenToken);
      $site->setWardenEncryptToken($wardenEncryptToken);
      $manager->saveDocument($site);
      $this->get('session')->getFlashBag()->add('notice', 'Your site has now been registered.');
    }
    else {
      $this->get('session')->getFlashBag()->add('error', 'Your site is already registered!');
    }

    return $this->redirect($this->generateUrl('sites_list'));
  }

  /**
   * Delete the site.
   *
   * @param int $id
   *   The site id to delete.
   * @param Request $request
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
   */
  public function DeleteAction($id, Request $request) {
    /** @var SiteManager $manager */
    $manager = $this->get('site_manager');
    $site = $manager->getDocumentById($id);

    $form = $this->createFormBuilder()
            ->add('Delete', 'submit', array(
              'attr' => array('class' => 'btn btn-danger')
            ))
            ->getForm();

    $form->handleRequest($request);

    if ($form->isValid()) {
      $manager->deleteDocument($id);
      $this->get('session')->getFlashBag()->add('notice', 'The site [' . $site->getName() . '] has been deleted.');

      return $this->redirect($this->generateUrl('sites_list'));
    }

    $params = array(
      'site' => $site,
      'form' => $form->createView(),
    );
    return $this->render('DeesonWardenBundle:Sites:delete.html.twig', $params);
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

    /** @var WardenRequestService $statusService */
    try {
      $statusService = $this->get('site_status_service');
      //$statusService->setConnectionTimeout(10);
      if ($site->getAuthUser() && $site->getAuthPass()) {
        $headers = array(sprintf('Authorization: Basic %s', base64_encode($site->getAuthUser() . ':' . $site->getAuthPass())));
        $statusService->setConnectionHeaders($headers);
      }
      $statusService->setSite($site);
      $statusService->processRequest();
    } catch (\Exception $e) {
      $this->get('session')->getFlashBag()->add('error', 'General Error - Unable to retrieve data from the site: ' . $e->getMessage());
      return $this->redirect($this->generateUrl('sites_show', array('id' => $id)));
    }

    $coreVersion = $statusService->getCoreVersion();
    $moduleData = $statusService->getModuleData();
    $siteName = $statusService->getSiteName();
    ksort($moduleData);
    $requestTime = $statusService->getRequestTime();

    /** @var SiteManager $manager */
    $manager = $this->get('site_manager');
    $site->setIsNew(FALSE);
    $site->setName($siteName);
    $site->setCoreVersion($coreVersion);
    $site->setModules($moduleData, TRUE);
    $manager->updateDocument();

    $this->get('session')->getFlashBag()->add('notice', 'This site has had it\'s core and module versions updated! This request took ' . $requestTime . ' secs.');

    return $this->redirect($this->generateUrl('sites_show', array('id' => $id)));
  }

  /*public function EditAction($id, Request $request) {
    /** @var SiteManager $manager *//*
    $manager = $this->get('site_manager');
    $site = $manager->getDocumentById($id);

    $form = $this->createFormBuilder($site)
            //->add('systemStatusToken', 'text')
            //->add('systemStatusEncryptToken', 'text')
            ->add('authUser', 'text')
            ->add('authPass', 'text')
            ->add('save', 'submit')
            ->getForm();

    $form->handleRequest($request);

    if ($form->isValid()) {
      $manager->updateDocument();
      $this->get('session')->getFlashBag()->add('notice', 'Site updated successfully');
    }

    $params = array(
      'site' => $site,
      'form' => $form->createView(),
    );

    return $this->render('DeesonWardenBundle:Sites:edit.html.twig', $params);
  }*/
}
