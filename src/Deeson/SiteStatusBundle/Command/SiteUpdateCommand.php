<?php

namespace Deeson\SiteStatusBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SiteUpdateCommand extends ContainerAwareCommand {

  protected $dm;

  protected function configure() {
    $this->setName('deeson:site-status:update')
      ->setDescription('Update the site status details');
      //->addArgument()
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
    $repository = $this->dm->getRepository('DeesonSiteStatusBundle:Site');
    $sites = $repository->findAll();

    foreach ($sites as $site) {
      /** @var \Deeson\SiteStatusBundle\Document\Site $site */
      $output->writeln('Updating site: ' . $site->getId() . ' - ' . $site->getUrl());

      /** @var StatusRequestService $statusService */
      $statusService = $this->getContainer()->get('site_status_service');
      //$statusService->setConnectionTimeout(10);
      $statusService->setSite($site);
      $statusService->requestSiteStatusData();

      $coreVersion = $statusService->getCoreVersion();
      $moduleData = $statusService->getModuleData();
      $requestTime = $statusService->getRequestTime();

      //$output->writeln('modules: ' . print_r($moduleData, TRUE));
      $output->writeln('request time: ' . $requestTime);

      $this->updateSite($site, array('coreVersion' => $coreVersion));

      $output->writeln('Update version: ' . $coreVersion);
    }
  }

  protected function updateSite($site, $siteData) {
    foreach ($siteData as $key => $value) {
      $method = 'set' . ucfirst($key);
      if (!method_exists($site, $method)) {
        //$this->get('session')->getFlashBag()->add('error', "Error: $method not valid on site object.");
        continue;
      }
      $site->$method($value);
    }

    $this->dm->flush();
  }

}