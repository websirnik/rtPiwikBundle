<?php

namespace rtPiwikBundle\Controller;

use rtPiwikBundle\Document\Metrics;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/piwik', name: 'piwikpage')]
    public function indexAction(): Response
    {
        return $this->render('@rtPiwik/Default/index.html.twig');
    }
}
