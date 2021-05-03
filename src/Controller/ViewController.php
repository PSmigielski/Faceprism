<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ViewController extends AbstractController
{
    /**
     * @Route("/{reactRouting}", name="home", defaults={"reactRouting":null})
     */
    public function index(): Response
    {
        return $this->render('base.html.twig');
    }
}
