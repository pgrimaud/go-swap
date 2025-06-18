<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PvPController extends AbstractController
{
    #[Route('/pvp', name: 'app_pvp_index')]
    public function index(): Response
    {
        return $this->render('pvp/index.html.twig');
    }
}
