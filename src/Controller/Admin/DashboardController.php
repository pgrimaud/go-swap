<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Move;
use App\Entity\Pokemon;
use App\Entity\PokemonMove;
use App\Entity\Type;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

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
        $websiteUrl = $this->urlGenerator->generate('app_home');
        yield MenuItem::linkToUrl('Back to website', 'fa fa-home', $websiteUrl);

        yield MenuItem::section('Content');
        yield MenuItem::linkToCrud('Pokemons', 'fas fa-list', Pokemon::class);

        yield MenuItem::section('Data');
        yield MenuItem::linkToCrud('Types', 'fas fa-shield', Type::class);
        yield MenuItem::linkToCrud('Moves', 'fas fa-fist-raised', Move::class);

        yield MenuItem::section('Relationships');
        yield MenuItem::linkToCrud('Pokemon Moves', 'fas fa-link', PokemonMove::class);
    }
}
