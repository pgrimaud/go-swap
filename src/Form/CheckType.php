<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

class CheckType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('picture', FileType::class, [
            'label' => 'Picture',
            'mapped' => false,
            'required' => true,
            'attr' => [
                'accept' => 'image/png, image/jpeg',
            ],
            'constraints' => [
                new File([
                    'maxSize' => '2M',
                    'mimeTypes' => [
                        'image/png',
                        'image/jpeg',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid PNG or JPEG image',
                ])
            ],
        ]);
    }
}
