<?php

namespace App\Controller\Admin;

use App\Entity\Type;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<Type>
 */
class TypeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Type::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            TextField::new('slug')->hideOnIndex(),
            ImageField::new('icon')
                ->setUploadDir('public/images/type')
                ->setBasePath('images/type')
                ->setUploadedFileNamePattern('[slug]-[randomhash].[extension]')
                ->setFormTypeOptions([
                    'attr' => [
                        'accept' => 'image/*',
                    ],
                ])
                ->setHelp('Check "*BORDERED" pictures <a target="_blank" href="https://github.com/PokeMiners/pogo_assets/tree/master/Images/Types">here</a>'),
        ];
    }
}
