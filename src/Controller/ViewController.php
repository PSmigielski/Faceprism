<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ViewController extends AbstractController
{
    /**
     * @Route("/{reactRouting}", name="react", defaults={"reactRouting":null})
     */
    public function index(): Response
    {
        return $this->render('base.html.twig');
    }
    /**
     * @Route("/reset", name="react_reset")
     */
    public function reset(): Response
    {
        return $this->render('base.html.twig');
    }
    /**
     * @Route("/verify", name="react_verify")
     */
    public function verify(): Response
    {
        return $this->render('base.html.twig');
    }
}
