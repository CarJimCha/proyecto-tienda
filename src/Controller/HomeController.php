<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index()
    {
        // Aquí puedes traer los objetos de la base de datos, pero por ahora lo dejamos simple
        return $this->render('index.html.twig');
    }
}
