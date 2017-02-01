<?php

namespace Deeson\WardenBundle\Controller;

use Deeson\WardenBundle\Document\ModuleDocument;
use Deeson\WardenBundle\Event\DashboardUpdateEvent;
use Deeson\WardenBundle\Event\SiteEvent;
use Deeson\WardenBundle\Event\SiteShowEvent;
use Deeson\WardenBundle\Event\SiteUpdateEvent;
use Deeson\WardenBundle\Event\WardenEvents;
use Deeson\WardenBundle\Managers\ModuleManager;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Deeson\WardenBundle\Managers\SiteManager;
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
    $sites = $manager->getDocumentsBy(array(), array('name' => 'asc'));

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

    /** @var EventDispatcher $dispatcher */
    $dispatcher = $this->get('event_dispatcher');

    $site = $manager->getDocumentById($id);

    $event = new SiteShowEvent($site);
    $dispatcher->dispatch(WardenEvents::WARDEN_SITE_SHOW, $event);

    $params = array(
      'site' => $site,
      'templates' => $event->getTemplates(),
    );

    foreach ($event->getParams() as $key => $value) {
      $params[$key] = $value;
    }

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
    $sslEncryptionService = $this->container->get('warden.ssl_encryption');

    $querySiteUrl = $sslEncryptionService->decrypt($request->query->get('data'));

    list($siteUrl, $wardenToken) = explode('|', $querySiteUrl);

    /** @var SiteManager $manager */
    $manager = $this->get('warden.site_manager');

    if (!$manager->urlExists($siteUrl)) {
      $site = $manager->makeNewItem();
      $site->setUrl($siteUrl);
      $site->setWardenToken($wardenToken);
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
    $manager = $this->get('warden.site_manager');
    /** @var SiteDocument $site */
    $site = $manager->getDocumentById($id);

    $form = $this->createFormBuilder()
      ->add('Delete', 'submit', array(
        'attr' => array('class' => 'btn btn-danger')
      ))
      ->getForm();

    $form->handleRequest($request);

    if ($form->isValid()) {
      $manager->deleteDocument($id);
      $this->updateModules($site);

      /** @var EventDispatcher $dispatcher */
      $dispatcher = $this->get('event_dispatcher');
      $event = new DashboardUpdateEvent($site, TRUE);
      $dispatcher->dispatch(WardenEvents::WARDEN_DASHBOARD_UPDATE, $event);

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
   * @return Response
   */
  public function publickeyAction() {
    /** @var SSLEncryptionService $sslEncryptionService */
    $sslEncryptionService = $this->container->get('warden.ssl_encryption');
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

    /** @var EventDispatcher $dispatcher */
    $dispatcher = $this->get('event_dispatcher');

    $event = new SiteEvent($site);

    try {
      $dispatcher->dispatch(WardenEvents::WARDEN_SITE_REFRESH, $event);
    }
    catch (\Exception $e) {
      $this->get('session')->getFlashBag()->add('error', 'General Error - Unable to retrieve data from the site: ' . $e->getMessage());
      return $this->redirect($this->generateUrl('sites_show', array('id' => $id)));
    }

    if ($event->hasMessage()) {
      $this->get('session')->getFlashBag()->add('notice', $event->getMessage());
    }

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
    $sslEncryptionService = $this->get('warden.ssl_encryption');

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
        throw new \Exception("Update {$wardenDataObject->url} : Bad timestamp - possible replay attack or the remote site server time is wrong.");
      }

      /** @var SiteDocument $site */
      $site = $siteManager->getDocumentBy(array('url' => $wardenDataObject->url));

      if (empty($site)) {
        // @todo have a proper exception here.
        throw new \Exception("Update {$wardenDataObject->url} : No such site registered with Warden: {$wardenDataObject->url}");
      }

      // Verify the key.
      if (empty($wardenDataObject->key) || $wardenDataObject->key !== $site->getWardenToken()) {
        // @todo have a proper exception here.
        throw new \Exception("Update {$wardenDataObject->url} : Site token does not match one stored for this site. {$wardenDataObject->key} : {$site->getWardenToken()}");
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
   * Update the modules to remove the site.
   *
   * @param \Deeson\WardenBundle\Document\SiteDocument $site
   */
  protected function updateModules(SiteDocument $site) {
    /** @var ModuleManager $moduleManager */
    $moduleManager = $this->get('warden.drupal.module');

    foreach ($site->getModules() as $siteModule) {
      /** @var ModuleDocument $module */
      $module = $moduleManager->findByProjectName($siteModule['name']);
      if (empty($module)) {
        print('Error getting module [' . $siteModule['name'] . ']');
        continue;
      }
      $module->removeSite($site->getId());
      $moduleManager->updateDocument();
    }
  }

  public function EditAction($id, Request $request) {
    /** @var SiteManager $manager */
    $manager = $this->get('warden.site_manager');
    $site = $manager->getDocumentById($id);

    $form = $this->createFormBuilder($site)
            ->add('name', 'text', array(
              'label' => 'Name: ',
              'attr' => array('size' => '40'),
            ))
            ->add('url', 'text', array(
              'label' => 'URL: ',
              'attr' => array('size' => '40'),
            ))
            ->add('wardenToken', 'text', array(
              'label' => 'Token: ',
              'attr' => array('size' => '80'),
            ))
            ->add('authUser', 'text', array(
              'label' => 'Auth User: ',
              'attr' => array('size' => '30'),
              'required' => false
            ))
            ->add('authPass', 'text', array(
              'label' => 'Auth Password: ',
              'attr' => array('size' => '30'),
              'required' => false
            ))
            ->add('isNew', 'checkbox', array(
              'required' => false
            ))
            ->add('save', 'submit', array(
              'attr' => array('class' => 'btn btn-danger')
            ))
            ->getForm();

    $form->handleRequest($request);

    if ($form->isValid()) {
      $manager->updateDocument();
      $this->get('session')->getFlashBag()->add('notice', 'Site updated successfully');
      return $this->redirect($this->generateUrl('sites_show', array('id' => $id)));
    }

    $params = array(
      'site' => $site,
      'form' => $form->createView(),
    );

    return $this->render('DeesonWardenBundle:Sites:edit.html.twig', $params);
  }
}
