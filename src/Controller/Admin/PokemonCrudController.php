<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Pokemon;
use App\Helper\GenerationHelper;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

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
                ->setHelp('Check normal pictures <a target="_blank" href="https://www.pokekalos.fr/pokedex/pokemongo/index.html">here</a>'),
            ImageField::new('shinyPicture')
                ->setUploadDir('public/images/pokemon/shiny')
                ->setBasePath('images/pokemon/shiny')
                ->setUploadedFileNamePattern('[slug]-[randomhash].[extension]')
                ->setFormTypeOptions([
                    'attr' => [
                        'accept' => 'image/*',
                    ],
                ])
                ->setHelp('Check shiny pictures <a target="_blank" href="https://www.pokekalos.fr/pokedex/pokemongo/index.html">here</a>'),

            AssociationField::new('types')
                ->setHelp('Select one or two types for this Pokémon')
                ->setFormTypeOptions([
                    'by_reference' => false,
                    'multiple' => true,
                ])->hideOnIndex(),

            BooleanField::new('shiny'),
            BooleanField::new('shadow'),
            BooleanField::new('lucky'),

            IntegerField::new('attack')->setHelp('Base attack stat')->hideOnIndex(),
            IntegerField::new('defense')->setHelp('Base defense stat')->hideOnIndex(),
            IntegerField::new('stamina')->setHelp('Base stamina (HP) stat')->hideOnIndex(),
        ];
    }
}
