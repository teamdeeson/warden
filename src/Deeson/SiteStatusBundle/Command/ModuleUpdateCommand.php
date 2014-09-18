<?php

namespace Deeson\SiteStatusBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Deeson\SiteStatusBundle\Managers\SiteManager;
use Deeson\SiteStatusBundle\Managers\ModuleManager;

class ModuleUpdateCommand extends ContainerAwareCommand {

  protected function configure() {
    $this->setName('deeson:site-status:update-modules')
      ->setDescription('Update the module details')
      ->addOption('import-new', NULL, InputOption::VALUE_NONE, 'If set will only import data on newly created modules');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    /** @var SiteManager $siteManager */
    $siteManager = $this->getContainer()->get('site_manager');
    /** @var ModuleManager $moduleManager */
    $moduleManager = $this->getContainer()->get('module_manager');

    if ($input->getOption('import-new')) {
      $sites = $siteManager->getDocumentsBy(array('isNew' => TRUE));
    }
    else {
      $sites = $siteManager->getAllDocuments();
    }

    foreach ($sites as $site) {
      /** @var \Deeson\SiteStatusBundle\Document\Site $site */
      $output->writeln('Updating site: ' . $site->getId() . ' - ' . $site->getUrl());

      foreach ($site->getModules() as $siteModule) {
        /** @var \Deeson\SiteStatusBundle\Document\Module $module */
        try {
          $module = $moduleManager->findByProjectName($siteModule['name']);
        } catch (\Deeson\SiteStatusBundle\Exception\DocumentNotFoundException $e) {
          $output->writeln('Error getting module [' . $siteModule['name'] . ']: ' . $e->getMessage());
          continue;
        }
        $moduleSites = $module->getSites();

        // Check if the site URL is already in the list for this module.
        if (is_array($moduleSites)) {
          $alreadyExists = FALSE;
          foreach ($moduleSites as $moduleSite) {
            if ($moduleSite['url'] == $site->getUrl()) {
              $alreadyExists = TRUE;
              break;
            }
          }
          if ($alreadyExists) {
            continue;
          }
        }

        $module->addSite($site->getId(), $site->getUrl(), $siteModule['version']);
        $moduleManager->updateDocument();
      }
    }
  }

}