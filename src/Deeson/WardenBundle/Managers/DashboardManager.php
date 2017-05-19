<?php

namespace Deeson\WardenBundle\Managers;

use Deeson\WardenBundle\Document\DashboardDocument;
use Deeson\WardenBundle\Document\ModuleDocument;
use Deeson\WardenBundle\Document\SiteDocument;
use Deeson\WardenBundle\Event\DashboardUpdateEvent;
use Deeson\WardenBundle\Services\MailService;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DashboardManager extends BaseManager {

  /**
   * @var MailService
   */
  protected $mailer;

  /**
   * @var ContainerInterface
   */
  protected $container;

  public function __construct($doctrine, Logger $logger, MailService $mailer, ContainerInterface $container) {
    parent::__construct($doctrine, $logger);
    $this->mailer = $mailer;
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
   * Event: warden.dashboard.update
   *
   * Fires when the dashboard might need to be updated.
   *
   * A check is done on the site to see if it should appear on the dashboard.
   *
   * @param DashboardUpdateEvent $event
   */
  public function onWardenDashboardUpdate(DashboardUpdateEvent $event) {
    /** @var SiteDocument $site */
    $site = $event->getSite();

    $qb = $this->createQueryBuilder();
    $qb->field('siteId')->equals(new \MongoId($site->getId()));
    $cursor = $qb->getQuery()->execute()->toArray();
    $dashboardSite = array_pop($cursor);
    if (!empty($dashboardSite)) {
      $this->logger->addInfo('Remove the site [' . $site->getName() . '] from the dashboard');
      $this->deleteDocument($dashboardSite->getId());
    }

    if ($event->isForceDelete()) {
      return;
    }

    $this->addSiteToDashboard($site);
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
    $hasCriticalIssue = $site->hasCriticalIssues();
    $modulesNeedUpdate = array();
    foreach ($site->getModules() as $siteModule) {
      if (!isset($siteModule['latestVersion'])) {
        continue;
      }
      if (ModuleDocument::isLatestVersion($siteModule)) {
        continue;
      }
      if (is_null($siteModule['version'])) {
        continue;
      }

      if ($siteModule['isSecurity']) {
        $hasCriticalIssue = TRUE;
      }

      $modulesNeedUpdate[] = $siteModule;
    }

    // Don't add the site to the dashboard if there are no critical issues.
    if (!$hasCriticalIssue) {
      return false;
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

    return true;
  }

  /**
   * Sends an email based upon the sites that listed on the dashboard.
   */
  public function sendUpdateEmail() {
    $this->logger->addInfo('Send email with list of sites on the dashboard');

    $to = $this->container->getParameter('warden.email.dashboard.alert_address');
    $from = $this->container->getParameter('warden.email.sender_address');
    $fromName = 'Warden';

    if (empty($to)) {
      $this->logger->addError('There is no value for "email_dashboard_alert_address" so the dashboard alert email can not be sent');
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
}
