<?php

namespace App\Controller\Admin;

use App\Entity\Pokemon;
use App\Helper\GenerationHelper;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;

class PokemonCrudController extends AbstractCrudController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AdminUrlGenerator $adminUrlGenerator
    )
    {
    }

    public static function getEntityFqcn(): string
    {
        return Pokemon::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $duplicate = Action::new('duplicate', 'Duplicate')
            ->addCssClass('text-info')
            ->linkToCrudAction('duplicate');

        $actions->add(Crud::PAGE_INDEX, $duplicate);

        return $actions;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setDefaultSort(['number' => 'ASC']);
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('number'),
            ChoiceField::new('generation')->setChoices(array_flip(GenerationHelper::GENERATIONS)),
            TextField::new('frenchName'),
            TextField::new('englishName'),
            TextField::new('form')->setHelp('Check forms <a target="_blank" href="https://pogoapi.net/api/v1/pokemon_types.json">here</a>'),
            BooleanField::new('isShiny'),
            BooleanField::new('isShinyThreeStars')->hideOnIndex(),
            BooleanField::new('isLucky'),
            BooleanField::new('isShadow'),
            BooleanField::new('isPurified'),
            ImageField::new('normalPicture')
                ->setUploadDir('public/images/normal')
                ->setBasePath('images/normal')
                ->setUploadedFileNamePattern('[slug]-[randomhash].[extension]')
                ->setFormTypeOptions([
                    'attr' => [
                        'accept' => 'image/*'
                    ]
                ])
                ->setHelp('Check normal pictures <a target="_blank" href="https://www.pokekalos.fr/pokedex/pokemongo/index.html">here</a>'),
            ImageField::new('shinyPicture')
                ->setUploadDir('public/images/shiny')
                ->setBasePath('images/shiny')
                ->setUploadedFileNamePattern('[slug]-[randomhash].[extension]')
                ->setFormTypeOptions([
                    'attr' => [
                        'accept' => 'image/*'
                    ]
                ])
                ->setHelp('Check shiny pictures <a target="_blank" href="https://www.pokekalos.fr/pokedex/pokemongo/index.html">here</a>'),
            AssociationField::new('evolutionChain')->autocomplete()->hideOnIndex()->setRequired(false),
            AssociationField::new('types')->autocomplete()->setRequired(true)->setFormTypeOption('by_reference', false),
        ];
    }

    public function duplicate(AdminContext $adminContext): Response
    {
        /** @var Pokemon $pokemon */
        $pokemon = $adminContext->getEntity()->getInstance();
        $newPokemon = clone $pokemon;

        try {
            $newPokemon->setNormalPicture(null);
            $newPokemon->setShinyPicture(null);

            $this->entityManager->persist($newPokemon);
            $this->entityManager->flush();

            $this->addFlash('success', sprintf('%s was duplicated', $pokemon->getFrenchName()));

            $redirectUrl = $this->adminUrlGenerator
                ->setController(PokemonCrudController::class)
                ->setAction(Action::DETAIL)
                ->setEntityId($newPokemon->getId())
                ->generateUrl();
        } catch (\Throwable $exception) {
            $this->addFlash('danger', $exception->getMessage());

            $redirectUrl = $this->adminUrlGenerator
                ->setController(PokemonCrudController::class)
                ->setAction(Action::DETAIL)
                ->setEntityId($pokemon->getId())
                ->generateUrl();
        }

        return $this->redirect($redirectUrl);
    }

}
