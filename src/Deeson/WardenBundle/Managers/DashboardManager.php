<?php

namespace Deeson\WardenBundle\Managers;

use Deeson\WardenBundle\Document\DashboardDocument;
use Deeson\WardenBundle\Document\ModuleDocument;
use Deeson\WardenBundle\Document\SiteDocument;
use Deeson\WardenBundle\Event\DashboardUpdateEvent;
use Deeson\WardenBundle\Services\MailService;
use Maknz\Slack\Client;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DashboardManager extends BaseManager {

  /**
   * @var MailService
   */
  protected $mailer;

  /**
   * @var SiteManager
   */
  protected $siteManager;

  /**
   * @var ContainerInterface
   */
  protected $container;

  public function __construct($doctrine, Logger $logger, SiteManager $siteManager, MailService $mailer, ContainerInterface $container) {
    parent::__construct($doctrine, $logger);
    $this->mailer = $mailer;
    $this->siteManager = $siteManager;
    $this->container = $container;
  }

  /**
   * @return string
   *   The type of this manager.
   *   e.g. 'DashboardDocument'
   */
  public function getType() {
    return 'DashboardDocument';
  }

  /**
   * Create a new empty type of the object.
   *
   * @return DashboardDocument
   */
  public function makeNewItem() {
    return new DashboardDocument();
  }

  /**
   * Event: warden.cron
   *
   * Fires on a cron event to update the dashboard
   */
  public function onWardenCron() {
    // Remove all 'have_issue' documents.
    $this->deleteAll();

    // Rebuild the dashboard based upon active sites.
    $sites = $this->siteManager->getDocumentsBy(array('isNew' => FALSE));
    foreach ($sites as $site) {
      /** @var SiteDocument $site */
      print('Checking site: ' . $site->getId() . ' - ' . $site->getUrl() . "\n");

      if ($this->updateDashboard($site)) {
        print('Adding site to dashboard: ' . $site->getId() . ' - ' . $site->getUrl() . "\n");
      }
    }
  }

  /**
   * Event: warden.dashboard.update
   *
   * Fires when the dashboard might need to be updated.
   *
   * A check is done on the site to see if it should appear on the dashboard.
   *
   * @param DashboardUpdateEvent $event
   */
  public function onWardenDashboardUpdate(DashboardUpdateEvent $event) {
    $this->updateDashboard($event->getSite(), $event->isForceDelete());
  }

  /**
   * Updates the dashboard for the relevant site.
   *
   * @param SiteDocument $site
   * @param bool $forceDelete
   *
   * @return bool
   */
  protected function updateDashboard(SiteDocument $site, $forceDelete = FALSE) {
    $qb = $this->createQueryBuilder();
    $qb->field('siteId')->equals(new \MongoId($site->getId()));
    $cursor = $qb->getQuery()->execute()->toArray();
    $dashboardSite = array_pop($cursor);
    if (!empty($dashboardSite)) {
      $this->logger->addInfo('Remove the site [' . $site->getName() . '] from the dashboard');
      $this->deleteDocument($dashboardSite->getId());
    }

    if ($forceDelete) {
      return FALSE;
    }

    return $this->addSiteToDashboard($site);
  }

  /**
   * Adds the site to the dashboard, if needed.
   *
   * @param SiteDocument $site
   *
   * @return bool
   *   True if the site has been added otherwise false.
   */
  public function addSiteToDashboard(SiteDocument $site) {
    $hasCriticalIssue = $site->getHasCriticalIssue();
    $modulesNeedUpdate = array();
    foreach ($site->getModules() as $siteModule) {
      if (!isset($siteModule['latestVersion'])) {
        continue;
      }
      if (is_null($siteModule['version'])) {
        continue;
      }
      if (ModuleDocument::isLatestVersion($siteModule)) {
        continue;
      }

      if ($siteModule['isSecurity']) {
        $hasCriticalIssue = TRUE;
      }

      $modulesNeedUpdate[] = $siteModule;
    }

    // Don't add the site to the dashboard if there are no critical issues.
    if (!$hasCriticalIssue) {
      return FALSE;
    }

    /** @var DashboardDocument $dashboard */
    $dashboard = $this->makeNewItem();
    $dashboard->setName($site->getName());
    $dashboard->setSiteId($site->getId());
    $dashboard->setUrl($site->getUrl());
    $dashboard->setCoreVersion($site->getCoreVersion(), $site->getLatestCoreVersion(), $site->getIsSecurityCoreVersion());
    $dashboard->setHasCriticalIssue($hasCriticalIssue);
    $dashboard->setAdditionalIssues($site->getAdditionalIssues());
    $dashboard->setModules($modulesNeedUpdate);

    $this->saveDocument($dashboard);

    return TRUE;
  }

  /**
   * Sends an email based upon the sites that listed on the dashboard.
   */
  public function sendEmailNotification() {
    $this->logger->addInfo('Send email with list of sites on the dashboard');

    $to = $this->container->getParameter('warden.email.dashboard.alert_address');
    $from = $this->container->getParameter('warden.email.sender_address');
    $fromName = 'Warden';

    if (empty($to)) {
      $this->logger->addError('There is no value for "warden.email.dashboard.alert_address" so the dashboard alert email can not be sent');
      return;
    }

    $dashboardSites = $this->getAllDocuments();

    $params = array(
      'sites' => $dashboardSites,
    );

    $sent = $this->mailer->sendEmail('dashboard', $params, $to, $from, $fromName);
    if ($sent) {
      $this->logger->addInfo('Email send to ' . $to . ' from ' . $from . ' with list of sites on the dashboard');
    }
    else {
      $this->logger->addError('Email failed to send to ' . $to . ' from ' . $from . ' with list of sites on the dashboard: ' . $this->mailer->getErrors());
    }
  }

  /**
   * Sends a notification to a Slack endpoint with the list of sites that need updating.
   */
  public function sendSlackNotification() {
    $this->logger->addInfo('Send Slack notification with list of sites on the dashboard');

    $slackHookUrl = $this->container->getParameter('warden.dashboard.slack.hook_url');

    if (empty($slackHookUrl)) {
      $this->logger->addError('There is no value for "warden.dashboard.slack.hook_url" so the dashboard notification to Slack can not be sent');
      return;
    }

    // @todo set the text for this via a variable/settings document?
    $message = "<!channel> Here is the full list of sites from Warden that need security updates applied:\n\n";

    $dashboardSites = $this->getAllDocuments();
    /** @var DashboardDocument $dashboardSite */
    foreach ($dashboardSites as $dashboardSite) {
      /** @var SiteDocument $site */
      $site = $this->siteManager->getDocumentById($dashboardSite->getSiteId());

      /** @todo handle this for being plugable - 2.0 */
      $modulesHaveSecurityUpdate = [];
      // Check if Core is out of date.
      if ($site->getIsSecurityCoreVersion()) {
        $modulesHaveSecurityUpdate[] = 'Drupal Core';
      }

      // Get a list of modules that have security updates.
      $moduleUpdates = $site->getModulesRequiringUpdates();
      foreach ($moduleUpdates as $module) {
        if (!$module['isSecurity']) {
          continue;
        }
        $modulesHaveSecurityUpdate[] = $module['name'];
      }
      $moduleUpdateList = implode(', ', $modulesHaveSecurityUpdate);

      $message .= ' - ' . $site->getName() . (!empty($moduleUpdateList) ? " ($moduleUpdateList)" : '' ) . "\n";
    }

    $client = new Client($slackHookUrl);
    $client->send($message);

    $this->logger->addInfo('Slack notification send to "' . $slackHookUrl . '" with list of sites on the dashboard');

  }
}
