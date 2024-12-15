<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FotosController extends AbstractController
{
    #[Route('/fotos', name: 'app_fotos')]
    public function index(): Response
    {
        return $this->render('fotos/index.html.twig', [
            'pagina' => 'fotos'
        ]);
    }
}
