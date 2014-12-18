<?php

namespace Deeson\WardenBundle\Controller;

use Deeson\WardenBundle\Event\SiteUpdateEvent;
use Deeson\WardenBundle\Document\SiteHaveIssueDocument;
use Deeson\WardenBundle\Event\WardenEvents;
use Deeson\WardenBundle\Managers\ModuleManager;
use Deeson\WardenBundle\Managers\SiteHaveIssueManager;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Deeson\WardenBundle\Managers\SiteManager;
use Deeson\WardenBundle\Services\WardenDrupalSiteService;
use Deeson\WardenBundle\Document\SiteDocument;
use Deeson\WardenBundle\Services\SSLEncryptionService;
use Symfony\Component\HttpFoundation\Response;

class SitesController extends Controller {

  /**
   * Default action for listing the sites available.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function IndexAction() {
    /** @var SiteManager $manager */
    $manager = $this->get('warden.site_manager');
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
    $manager = $this->get('warden.site_manager');
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
    /** @var SSLEncryptionService $sslEncryptionService */
    $sslEncryptionService = $this->container->get('ssl_encryption');

    $querySiteUrl = $sslEncryptionService->decrypt($request->query->get('data'));

    list($siteUrl, $wardenToken) = explode('|', $querySiteUrl);

    /** @var SiteManager $manager */
    $manager = $this->get('warden.site_manager');

    if (!$manager->urlExists($siteUrl)) {
      $site = $manager->makeNewItem();
      $site->setUrl($siteUrl);
      $site->setWardenToken($wardenToken);
      $manager->saveDocument($site);
      $this->get('session')
        ->getFlashBag()
        ->add('notice', 'Your site has now been registered.');
    }
    else {
      $this->get('session')
        ->getFlashBag()
        ->add('error', 'Your site is already registered!');
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
    $manager = $this->get('warden.site_manager');
    $site = $manager->getDocumentById($id);

    $form = $this->createFormBuilder()
      ->add('Delete', 'submit', array(
        'attr' => array('class' => 'btn btn-danger')
      ))
      ->getForm();

    $form->handleRequest($request);

    if ($form->isValid()) {
      $manager->deleteDocument($id);
      $this->updateDashboard($site, TRUE);
      $this->get('session')
        ->getFlashBag()
        ->add('notice', 'The site [' . $site->getName() . '] has been deleted.');

      return $this->redirect($this->generateUrl('sites_list'));
    }

    $params = array(
      'site' => $site,
      'form' => $form->createView(),
    );
    return $this->render('DeesonWardenBundle:Sites:delete.html.twig', $params);
  }

  /**
   * @return Response
   */
  public function publickeyAction() {
    /** @var SSLEncryptionService $sslEncryptionService */
    $sslEncryptionService = $this->container->get('ssl_encryption');
    $publicKey = base64_encode($sslEncryptionService->getPublicKey());
    return new Response($publicKey, 200, array('Content-Type: text/plain'));
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
    $manager = $this->get('warden.site_manager');
    /** @var SiteDocument $site */
    $site = $manager->getDocumentById($id);

    try {
      /** @var WardenDrupalSiteService $statusService */
      $statusService = $this->get('warden.drupal.site');

      //$statusService->setConnectionTimeout(10);
      if ($site->getAuthUser() && $site->getAuthPass()) {
        $headers = array(sprintf('Authorization: Basic %s', base64_encode($site->getAuthUser() . ':' . $site->getAuthPass())));
        $statusService->setConnectionHeaders($headers);
      }

      $statusService->setSite($site);
      $statusService->processRequest();
    } catch (\Exception $e) {
      $this->updateDashboard($site);
      $this->get('session')
        ->getFlashBag()
        ->add('error', 'General Error - Unable to retrieve data from the site: ' . $e->getMessage());
      return $this->redirect($this->generateUrl('sites_show', array('id' => $id)));
    }

    $requestTime = $statusService->getRequestTime();
    $this->get('session')
      ->getFlashBag()
      ->add('notice', 'This site has had it\'s core and module versions updated! This request took ' . $requestTime . ' secs.');
    return $this->redirect($this->generateUrl('sites_show', array('id' => $id)));
  }

  /**
   * Endpoint for a site to update itself
   *
   * @param Request $request
   * @return Response
   */
  public function updateAction(Request $request) {
    /** @var Logger $logger */
    $logger = $this->get('logger');

    /** @var SiteManager $siteManager */
    $siteManager = $this->get('warden.site_manager');

    /** @var SSLEncryptionService $sslEncryptionService */
    $sslEncryptionService = $this->get('ssl_encryption');

    /** @var EventDispatcher $dispatcher */
    $dispatcher = $this->get('event_dispatcher');

    try {
      $wardenDataObject = $sslEncryptionService->decrypt($request->getContent());

      if (!is_object($wardenDataObject) || !isset($wardenDataObject->core)) {
        throw new \Exception("Invalid update request");
      }

      // Verify the request timestamp.
      $time = time();
      if (empty($wardenDataObject->time)
        || ($wardenDataObject->time > ($time + 20))
        || ($wardenDataObject->time < ($time - 20))
      ) {
        throw new \Exception("Bad timestamp - possible replay attack");
      }

      /** @var SiteDocument $site */
      $site = $siteManager->getDocumentBy(array('url' => $wardenDataObject->url));

      if (empty($site)) {
        throw new \Exception("No such site registered with Warden: {$wardenDataObject->url}");
      }

      // Verify the key.
      if (empty($wardenDataObject->key) || $wardenDataObject->key !== $site->getWardenToken()) {
        throw new \Exception("Site token does not match one stored for this site. {$wardenDataObject->key} : {$site->getWardenToken()}");
      }

      $event = new SiteUpdateEvent($site, $wardenDataObject);
      $dispatcher->dispatch(WardenEvents::WARDEN_SITE_UPDATE, $event);

      $siteManager->updateDocument();

      return new Response('OK', 200, array('Content-Type: text/plain'));

    } catch (\Exception $e) {
      $logger->addError($e->getMessage());
      return new Response('Bad Request', 400, array('Content-Type: text/plain'));
    }
  }

  /**
   * Updates the dashboard following an update to a site.
   *
   * @param SiteDocument $site
   *   The site object to update the dashboard for.
   * @param bool $forceDelete
   *   If true, then the site will just be deleted from the dashboard.
   *
   * @throws \Doctrine\ODM\MongoDB\MongoDBException
   */
  protected function updateDashboard(SiteDocument $site, $forceDelete = FALSE) {
    /** @var SiteHaveIssueManager $siteHaveIssueManager */
    $siteHaveIssueManager = $this->get('site_have_issue_manager');

    /** @var SiteHaveIssueManager $issueSite */
    $qb = $siteHaveIssueManager->createQueryBuilder();
    $qb->field('siteId')->equals(new \MongoId($site->getId()));
    $cursor = $qb->getQuery()->execute()->toArray();
    $issueSite = array_pop($cursor);
    if (empty($issueSite)) {
      return;
    }
    $siteHaveIssueManager->deleteDocument($issueSite->getId());

    if ($forceDelete) {
      return;
    }

    $isModuleSecurityUpdate = FALSE;
    $modulesNeedUpdate = array();
    foreach ($site->getModules() as $siteModule) {
      if (!isset($siteModule['latestVersion'])) {
        continue;
      }
      if ($siteModule['version'] == $siteModule['latestVersion']) {
        continue;
      }
      if (is_null($siteModule['version'])) {
        continue;
      }

      if ($siteModule['isSecurity']) {
        $isModuleSecurityUpdate = TRUE;
      }

      $modulesNeedUpdate[] = $siteModule;
    }

    if ($site->getLatestCoreVersion() == $site->getCoreVersion() && !$isModuleSecurityUpdate) {
      return;
    }

    /** @var SiteHaveIssueDocument $needUpdate */
    $needUpdate = $siteHaveIssueManager->makeNewItem();
    $needUpdate->setName($site->getName());
    $needUpdate->setSiteId($site->getId());
    $needUpdate->setUrl($site->getUrl());
    $needUpdate->setCoreVersion($site->getCoreVersion(), $site->getLatestCoreVersion(), $site->getIsSecurityCoreVersion());
    $needUpdate->setAdditionalIssues($site->getAdditionalIssues());
    $needUpdate->setModules($modulesNeedUpdate);

    $siteHaveIssueManager->saveDocument($needUpdate);
  }

  /*public function EditAction($id, Request $request) {
    /** @var SiteManager $manager *//*
    $manager = $this->get('warden.site_manager');
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
