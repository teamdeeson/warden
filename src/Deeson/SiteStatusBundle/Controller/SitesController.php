<?php

namespace Deeson\SiteStatusBundle\Controller;

use Deeson\SiteStatusBundle\Exception\SitesStatusException;
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
    $site = $this->getSiteData($id);
    //printf('<pre>%s</pre>', print_r($site_data, true));

    $site_status_url = $site->getUrl() . '/admin/reports/system_status/' . $site->getSystemStatusToken();

    /*$buzz = $this->container->get('buzz');
    $request = $buzz->get($site_status_url);
    $data_request = $request->getContent();

    //printf('<pre>req: %s</pre>', print_r($data_request, true));
    $data_request_object = json_decode($data_request);
    //printf('<pre>req obj: %s</pre>', print_r($data_request_object, true));
    if (is_string($data_request_object->system_status) && $data_request_object->system_status == 'encrypted') {
      $system_status_data = $this->decrypt($data_request_object->data, $site->getSystemStatusEncryptToken());
      $system_status_data_object = json_decode($system_status_data);
    }
    else {
      throw new SitesStatusException('Request is not encrypted!');
      $system_status_data_object = $data_request_object->system_status;
      // This request isn't encrypted so don't do anything with it but generate an alert?
    }*/
    //printf('<pre>%s</pre>', print_r($system_status_data_object, true);
    /*$core_version = $system_status_data_object->system_status->core->drupal->version;

    if ($site->getCoreVersion() !== '') {
      print 'update core';
      $this->updateSite($id, array('coreVersion' => $core_version));
    }*/

    $params = array(
      'site' => $site,
      'status_data' => '', //print_r($system_status_data_object, true),
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
    $query_site_url = $request->query->get('siteUrl');
    list($site_url, $system_status_token, $system_status_encrypt_token) = explode('|', $query_site_url);

    $dm = $this->getDoctrineManager();
    $repository = $this->getDoctrineRepository($dm);
    $sites_by_url = $repository->findBy(array('url' => $site_url));

    if ($sites_by_url->count() < 1) {
      $site = new Site();
      $site->setUrl($site_url);
      $site->setSystemStatusToken($system_status_token);
      $site->setSystemStatusEncryptToken($system_status_encrypt_token);

      $dm->persist($site);
      $dm->flush();

      $this->get('session')->getFlashBag()->add('notice', 'Your site has now been registered.');
    }
    else {
      $this->get('session')->getFlashBag()->add('error', 'Your site is already registered!');
    }

    //printf('<pre>%s</pre>', print_r($site, true));
    //die();
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

    $site_status_url = $site->getUrl() . '/admin/reports/system_status/' . $site->getSystemStatusToken();

    $buzz = $this->container->get('buzz');
    $request = $buzz->get($site_status_url);
    $data_request = $request->getContent();

    //printf('<pre>req: %s</pre>', print_r($data_request, true));
    $data_request_object = json_decode($data_request);
    //printf('<pre>req obj: %s</pre>', print_r($data_request_object, true));
    if (is_string($data_request_object->system_status) && $data_request_object->system_status == 'encrypted') {
      $system_status_data = $this->decrypt($data_request_object->data, $site->getSystemStatusEncryptToken());
      $system_status_data_object = json_decode($system_status_data);
    }
    else {
      throw new SitesStatusException('Request is not encrypted!');
      $system_status_data_object = $data_request_object->system_status;
      // This request isn't encrypted so don't do anything with it but generate an alert?
    }
    //printf('<pre>%s</pre>', print_r($system_status_data_object, true);
    $core_version = $system_status_data_object->system_status->core->drupal->version;
    $this->updateSite($id, array('coreVersion' => $core_version));

    $this->get('session')->getFlashBag()->add('notice', 'Your site has had the core version updated!');

    return $this->redirect('/sites/' . $id);
  }

  /**
   * Update the site details.
   *
   * @param $id
   * @param $site_data
   */
  protected function updateSite($id, $site_data) {
    $dm = $this->getDoctrineManager();
    $site = $this->getDoctrineRepository($dm)->find($id);

    foreach ($site_data as $key => $value) {
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


  /**
   * System Status: decrypt an encrypted message.
   */
  protected function decrypt($ciphertext_base64, $encrypt_token) {
    $key = hash("SHA256", $encrypt_token, TRUE);
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
    $ciphertext_dec = base64_decode($ciphertext_base64);
    $iv_dec = substr($ciphertext_dec, 0, $iv_size);
    $ciphertext_dec = substr($ciphertext_dec, $iv_size);
    $plaintext_dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);

    //return $plaintext_dec;
    return utf8_decode(trim($plaintext_dec));
  }
}
