<?php

namespace App\Controller\Admin;

use App\Entity\EvolutionChain;
use App\Entity\Pokemon;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DashboardController extends AbstractDashboardController
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        /* @phpstan-ignore-next-line */
        return $this->redirect($adminUrlGenerator->setController(PokemonCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Go Swap');
    }

    public function configureMenuItems(): iterable
    {
        $websiteUrl = $this->urlGenerator->generate('app_index');
        yield MenuItem::linkToUrl('Back to website', 'fa fa-home', $websiteUrl);
        yield MenuItem::section('Admin');
        yield MenuItem::linkToCrud('Pokemons', 'fas fa-list', Pokemon::class);
        yield MenuItem::linkToCrud('Evolution chains', 'fas fa-link', EvolutionChain::class);
    }
}
