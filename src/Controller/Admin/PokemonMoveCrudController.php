<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\PokemonMove;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

/**
 * @extends AbstractCrudController<PokemonMove>
 */
class PokemonMoveCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PokemonMove::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular('Pokemon Move')
            ->setEntityLabelInPlural('Pokemon Moves')
            ->setPageTitle('index', 'Manage Pokemon Moves')
            ->setDefaultSort(['pokemon' => 'ASC']);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('pokemon'))
            ->add(EntityFilter::new('move'))
            ->add('elite');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('pokemon')
                ->setHelp('Select the Pokémon')
                ->autocomplete(),

            AssociationField::new('move')
                ->setHelp('Select the move (Fast or Charged)')
                ->autocomplete(),

            BooleanField::new('elite')
                ->setHelp('Is this an Elite move?'),
        ];
    }
}
