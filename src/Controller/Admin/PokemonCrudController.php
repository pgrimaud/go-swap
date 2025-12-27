<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Pokemon;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
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
            TextField::new('name'),
            BooleanField::new('shiny'),
            BooleanField::new('shadow'),
            BooleanField::new('lucky'),
            TextField::new('form')->setHelp('Check forms <a target="_blank" href="https://pogoapi.net/api/v1/pokemon_types.json">here</a>')->onlyOnForms(),
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
        ];
    }
}
