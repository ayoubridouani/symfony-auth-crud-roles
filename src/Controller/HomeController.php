<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/', name: 'home')]
#[IsGranted('ROLE_USER')]
class HomeController extends AbstractController{
    #[Route('', name: '')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }
}
