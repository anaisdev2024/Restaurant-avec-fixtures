<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    
  #[Route('/', name: 'app_home_index')]     

    public function home() : Response
    {
  return new Response('Bienvenue sur votre accueil !');
     }

    #[Route('/{id}', name: 'edit', methods: 'PUT', requirements: ['id' => '\d+'])]
    public function edit(int $id): Response
    {
        return new Response('Edit page');
    }
}