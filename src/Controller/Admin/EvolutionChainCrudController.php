<?php

namespace App\Controller\Admin;

use App\Entity\EvolutionChain;
use App\Entity\Pokemon;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class EvolutionChainCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return EvolutionChain::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            AssociationField::new('pokemons')
                ->setFormTypeOption('choice_label', 'french_name')
                ->setFormTypeOption('by_reference', false),
        ];
    }
}
