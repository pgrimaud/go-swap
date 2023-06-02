<?php

namespace App\Controller\Admin;

use App\Entity\Pokemon;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PokemonCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Pokemon::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setDefaultSort(['number' => 'ASC'])
            ;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('number'),
            TextField::new('generation'),
            TextField::new('frenchName'),
            TextField::new('englishName'),
            BooleanField::new('isShiny'),
            ImageField::new('normalPicture')
                ->setUploadDir('public/images/normal')
                ->setBasePath('images/normal')
                ->setUploadedFileNamePattern('[slug]-[randomhash].[extension]')
                ->setFormTypeOptions([
                    'attr' => [
                        'accept' => 'image/*'
                    ]
                ]),
            ImageField::new('shinyPicture')
                ->setUploadDir('public/images/shiny')
                ->setBasePath('images/shiny')
                ->setUploadedFileNamePattern('[slug]-[randomhash].[extension]')
                ->setFormTypeOptions([
                    'attr' => [
                        'accept' => 'image/*'
                    ]
                ]),
            AssociationField::new('evolutionChain')->autocomplete()->hideOnIndex(),
        ];
    }

}
