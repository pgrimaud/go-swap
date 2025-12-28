<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Move;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<Move>
 */
class MoveCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Move::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setDefaultSort(['name' => 'ASC'])
            ->setPageTitle('index', 'Moves')
            ->setPageTitle('new', 'Create Move')
            ->setPageTitle('edit', 'Edit Move');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            TextField::new('slug')->hideOnIndex(),

            ChoiceField::new('class')
                ->setChoices([
                    'Charged' => Move::CHARGED_MOVE,
                    'Fast' => Move::FAST_MOVE,
                ])->setLabel('Attack type'),

            AssociationField::new('type'),

            TextField::new('category')->onlyOnForms(),

            IntegerField::new('power')->setHelp('Damage dealt')->hideOnIndex(),
            IntegerField::new('energy')->setHelp('Energy cost (charged moves)')->hideOnIndex(),
            IntegerField::new('energyGain')->setHelp('Energy gained (fast moves)')->hideOnIndex(),
            IntegerField::new('cooldown')->setHelp('Duration in turns (ms)')->hideOnIndex(),

            IntegerField::new('buffAttack')
                ->setHelp('Attack buff stages (-4 to +4)')
                ->hideOnIndex(),
            IntegerField::new('buffDefense')
                ->setHelp('Defense buff stages (-4 to +4)')
                ->hideOnIndex(),
            ChoiceField::new('buffTarget')
                ->setChoices([
                    'Self' => Move::BUFF_TARGET_SELF,
                    'Opponent' => Move::BUFF_TARGET_OPPONENT,
                ])
                ->hideOnIndex(),
            NumberField::new('buffChance')
                ->setHelp('Probability of buff triggering (0.0 to 1.0)')
                ->hideOnIndex(),
        ];
    }
}
