<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'helloWorld' => 'weshalote',
        ]);
    }

    /**
     * @Route("/informations", name="mesInformations")
     */
    public function mesInformations(): Response
    {
        return $this->render('mes-informations/index.html.twig', [
            'controller_name' => 'HomeController',
            'helloWorld' => 'weshalote',
            'route_name' => 'mesInformations'
        ]);
    }
}
