<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Pokemon;
use App\Entity\PokemonMove;
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
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @extends AbstractCrudController<Pokemon>
 */
class PokemonCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Pokemon::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setDefaultSort(['number' => 'ASC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $duplicate = Action::new('duplicate', 'Duplicate', 'fa fa-copy')
            ->linkToCrudAction('duplicatePokemon');

        return parent::configureActions($actions)
            ->add(Crud::PAGE_INDEX, $duplicate)
            ->add(Crud::PAGE_DETAIL, $duplicate);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('number'),

            ChoiceField::new('generation')
                ->setChoices([
                    'Kanto (#1-151)' => GenerationHelper::GENERATION_KANTO,
                    'Johto (#152-251)' => GenerationHelper::GENERATION_JOHTO,
                    'Hoenn (#252-386)' => GenerationHelper::GENERATION_HOENN,
                    'Sinnoh (#387-493)' => GenerationHelper::GENERATION_SINNOH,
                    'Unova (#494-649)' => GenerationHelper::GENERATION_UNOVA,
                    'Kalos (#650-721)' => GenerationHelper::GENERATION_KALOS,
                    'Alola (#722-809)' => GenerationHelper::GENERATION_ALOLA,
                    'Galar (#810-898)' => GenerationHelper::GENERATION_GALAR,
                    'Hisui (#899-905)' => GenerationHelper::GENERATION_HISUI,
                    'Paldea (#906+)' => GenerationHelper::GENERATION_PALDEA,
                    'Unidentified (Meltan #808-809)' => GenerationHelper::GENERATION_UNIDENTIFIED,
                ])
                ->hideOnIndex(),

            TextField::new('name'),
            TextField::new('slug')->hideOnIndex(),

            TextField::new('form')
                ->setHelp('Check forms <a target="_blank" href="https://pogoapi.net/api/v1/pokemon_types.json">here</a>')
                ->hideOnIndex(),

            ImageField::new('picture')
                ->setUploadDir('public/images/pokemon/normal')
                ->setBasePath('images/pokemon/normal')
                ->setUploadedFileNamePattern('[slug]-[randomhash].[extension]')
                ->setFormTypeOptions([
                    'attr' => [
                        'accept' => 'image/*',
                    ],
                ])
                ->setHelp('Check normal pictures <a target="_blank" href="https://db.pokemongohub.net/">here</a>, format is <a target="_blank" href="https://db.pokemongohub.net/images/ingame/normal/pm854.fPHONY.icon.png"></a>'),
            ImageField::new('shinyPicture')
                ->setUploadDir('public/images/pokemon/shiny')
                ->setBasePath('images/pokemon/shiny')
                ->setUploadedFileNamePattern('[slug]-[randomhash].[extension]')
                ->setFormTypeOptions([
                    'attr' => [
                        'accept' => 'image/*',
                    ],
                ])
                ->setHelp('Check shiny pictures <a target="_blank" href="https://db.pokemongohub.net/">here</a>, format is <a target="_blank" href="https://db.pokemongohub.net/images/ingame/normal/pm854.fPHONY.s.icon.png"></a>'),

            AssociationField::new('types')
                ->setHelp('Select one or two types for this Pokémon')
                ->setFormTypeOptions([
                    'by_reference' => false,
                    'multiple' => true,
                ])->hideOnIndex(),

            AssociationField::new('evolutionChain', 'Evolution Chain')
                ->setHelp('Select the evolution chain this Pokémon belongs to')
                ->hideOnIndex(),

            BooleanField::new('shiny'),
            BooleanField::new('shadow'),
            BooleanField::new('lucky'),

            IntegerField::new('attack')->setHelp('Base attack stat')->hideOnIndex(),
            IntegerField::new('defense')->setHelp('Base defense stat')->hideOnIndex(),
            IntegerField::new('stamina')->setHelp('Base stamina (HP) stat')->hideOnIndex(),
        ];
    }

    public function duplicatePokemon(
        AdminContext $context,
        EntityManagerInterface $entityManager,
        AdminUrlGenerator $adminUrlGenerator,
    ): RedirectResponse {
        // Get entityId from request instead of context
        $entityId = $context->getRequest()->query->get('entityId');

        if (!$entityId) {
            $this->addFlash('error', 'Pokemon ID not found');

            return $this->redirect($adminUrlGenerator->setController(self::class)->setAction(Action::INDEX)->generateUrl());
        }

        /** @var Pokemon|null $originalPokemon */
        $originalPokemon = $entityManager->getRepository(Pokemon::class)->find($entityId);

        if (!$originalPokemon) {
            $this->addFlash('error', 'Pokemon not found');

            return $this->redirect($adminUrlGenerator->setController(self::class)->setAction(Action::INDEX)->generateUrl());
        }

        $duplicatedPokemon = new Pokemon();
        $duplicatedPokemon->setNumber($originalPokemon->getNumber() ?? 0);
        $duplicatedPokemon->setName($originalPokemon->getName() . ' (Copy)');
        $duplicatedPokemon->setSlug($originalPokemon->getSlug() . '-copy-' . time());
        $duplicatedPokemon->setGeneration($originalPokemon->getGeneration() ?? '');
        $duplicatedPokemon->setForm($originalPokemon->getForm());
        $duplicatedPokemon->setAttack($originalPokemon->getAttack() ?? 0);
        $duplicatedPokemon->setDefense($originalPokemon->getDefense() ?? 0);
        $duplicatedPokemon->setStamina($originalPokemon->getStamina() ?? 0);
        $duplicatedPokemon->setHash(md5(uniqid((string) rand(), true)));
        $duplicatedPokemon->setShadow($originalPokemon->isShadow());
        $duplicatedPokemon->setShiny($originalPokemon->isShiny());
        $duplicatedPokemon->setLucky($originalPokemon->isLucky());
        $duplicatedPokemon->setEvolutionChain($originalPokemon->getEvolutionChain());

        foreach ($originalPokemon->getTypes() as $type) {
            $duplicatedPokemon->addType($type);
        }

        // Duplicate moves
        foreach ($originalPokemon->getPokemonMoves() as $originalPokemonMove) {
            $duplicatedPokemonMove = new PokemonMove();
            $duplicatedPokemonMove->setPokemon($duplicatedPokemon);
            $duplicatedPokemonMove->setMove($originalPokemonMove->getMove());
            $duplicatedPokemonMove->setElite($originalPokemonMove->isElite());
            $duplicatedPokemon->addPokemonMove($duplicatedPokemonMove);
        }

        $entityManager->persist($duplicatedPokemon);
        $entityManager->flush();

        $this->addFlash('success', sprintf('Pokémon "%s" has been duplicated successfully!', $originalPokemon->getName()));

        $url = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::EDIT)
            ->setEntityId($duplicatedPokemon->getId())
            ->generateUrl();

        return $this->redirect($url);
    }
}
