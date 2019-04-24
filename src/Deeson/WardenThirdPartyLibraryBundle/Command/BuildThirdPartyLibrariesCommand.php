<?php

namespace Deeson\WardenThirdPartyLibraryBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Deeson\WardenThirdPartyLibraryBundle\Services\ThirdPartyLibraryService;

class BuildThirdPartyLibrariesCommand extends ContainerAwareCommand {

  protected function configure() {
    $this->setName('deeson:warden-third-party-library:build-libraries')
      ->setDescription('Builds list of third party libraries that are used by the sites.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    /** @var ThirdPartyLibraryService $thirdPartyLibraryService */
    $thirdPartyLibraryService = $this->getContainer()->get('warden.third_party_library.library_service');
    $thirdPartyLibraryService->buildList();
  }

}
