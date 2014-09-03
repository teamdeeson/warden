<?php

namespace Deeson\SiteStatusBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller {

  public function indexAction() {
    return $this->render('DeesonSiteStatusBundle:Default:index.html.twig');
  }

}
