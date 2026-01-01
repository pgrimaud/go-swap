<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\CustomList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<CustomList>
 */
class CustomListType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'List Name',
                'attr' => [
                    'placeholder' => 'e.g. My Shiny Collection',
                    'class' => 'w-full px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-950 text-gray-900 dark:text-white focus:ring-2 focus:ring-violet-600 focus:border-transparent',
                ],
                'constraints' => [
                    new NotBlank(
                        message: 'Please enter a name for your list.',
                    ),
                    new Length(
                        min: 3,
                        max: 255,
                        minMessage: 'The list name must be at least {{ limit }} characters long.',
                        maxMessage: 'The list name cannot be longer than {{ limit }} characters.',
                    ),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Add a description for your list (optional)',
                    'rows' => 3,
                    'class' => 'w-full px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-950 text-gray-900 dark:text-white focus:ring-2 focus:ring-violet-600 focus:border-transparent',
                ],
            ])
            ->add('isPublic', CheckboxType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'class' => 'w-4 h-4 rounded border-gray-300 text-violet-600 focus:ring-2 focus:ring-violet-600',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CustomList::class,
        ]);
    }
}
