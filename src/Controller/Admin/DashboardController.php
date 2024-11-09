<?php

namespace App\Controller\Admin;

use App\Entity\EvolutionChain;
use App\Entity\Pokemon;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private KernelInterface $kernel
    )
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

    #[Route('/admin/update-pictures', name: 'admin_update_pictures')]
    public function updatePictures(): Response
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(['command' => 'app:download-pictures']);

        $output = new BufferedOutput();
        $application->run($input, $output);
        
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        /* @phpstan-ignore-next-line */
        return $this->redirect($adminUrlGenerator->setController(PokemonCrudController::class)->generateUrl());
    }

    public function configureMenuItems(): iterable
    {
        $websiteUrl = $this->urlGenerator->generate('app_index');
        yield MenuItem::linkToUrl('Back to website', 'fa fa-home', $websiteUrl);
        yield MenuItem::section('Admin');
        yield MenuItem::linkToCrud('Pokemons', 'fas fa-list', Pokemon::class);
        yield MenuItem::linkToCrud('Evolution chains', 'fas fa-link', EvolutionChain::class);
        yield MenuItem::section('Commands');
        yield MenuItem::linkToUrl('Update pictures', 'fa fa-image', $this->urlGenerator->generate('admin_update_pictures'));
    }
}
