<?php

namespace PiwikBundle\Controller;

use PiwikBundle\Document\Metrics;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/piwik", name="piwikpage")
     */
    public function indexAction()
    {

        return $this->render('PiwikBundle:Default:index.html.twig');
    }
}
