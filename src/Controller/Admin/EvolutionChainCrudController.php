<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\EvolutionChain;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<EvolutionChain>
 */
class EvolutionChainCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return EvolutionChain::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('chainId', 'Chain ID'),
            TextField::new('basePokemonName', 'Base Pokemon'),
            AssociationField::new('pokemon', 'Pokemon')
                ->hideOnIndex(),
        ];
    }
}
