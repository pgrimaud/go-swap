<?php

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
            ChoiceField::new('generation')->setChoices(array_flip(GenerationHelper::GENERATIONS)),
            TextField::new('frenchName'),
            TextField::new('englishName'),
            BooleanField::new('isShiny'),
            BooleanField::new('isLucky'),
            ImageField::new('normalPicture')
                ->setUploadDir('public/images/normal')
                ->setBasePath('images/normal')
                ->setUploadedFileNamePattern('[slug]-[randomhash].[extension]')
                ->setFormTypeOptions([
                    'attr' => [
                        'accept' => 'image/*'
                    ]
                ])
                ->setHelp('Check normal pictures <a target="_blank" href="https://www.pokebip.com/page/jeuxvideo/pokemon_go/pokemon">here</a>'),
            ImageField::new('shinyPicture')
                ->setUploadDir('public/images/shiny')
                ->setBasePath('images/shiny')
                ->setUploadedFileNamePattern('[slug]-[randomhash].[extension]')
                ->setFormTypeOptions([
                    'attr' => [
                        'accept' => 'image/*'
                    ]
                ])
                ->setHelp('Check shiny pictures <a target="_blank" href="https://www.pokebip.com/page/jeuxvideo/pokemon_go/pokemon_chromatiques">here</a>'),
            AssociationField::new('evolutionChain')->autocomplete()->hideOnIndex()->setRequired(false),
        ];
    }

}
