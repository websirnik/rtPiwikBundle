<?php

namespace rtPiwikBundle\Controller;

use rtPiwikBundle\Document\Metrics;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/piwik", name="piwikpage")
     */
    public function indexAction()
    {
        return $this->render('rtPiwikBundle:Default:index.html.twig');
    }
}
