<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => [
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'class' => 'form-control rounded-md shadow-sm border-gray-300
                         focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50',
                    ],
                ],
                'first_options' => [
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuillez saisir un mot de passe',
                        ]),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'Votre mot de passe doit comporter au moins {{ limit }} caractères',
                            'max' => 4096,
                        ]),
                    ],
                    'label' => 'Nouveau mot de passe',
                    'attr' => [
                        'class' => 'form-control mb-3 rounded-md shadow-sm border-gray-300 focus:border-indigo-500
                         focus:ring focus:ring-indigo-200 focus:ring-opacity-50',
                    ],
                    'label_attr' => [
                        'class' => 'block text-sm font-medium text-gray-700 mb-1'
                    ]
                ],
                'second_options' => [
                    'label' => 'Répétez le mot de passe',
                    'attr' => [
                        'class' => 'form-control mb-3 rounded-md shadow-sm border-gray-300
                         focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50',
                    ],
                    'label_attr' => [
                        'class' => 'block text-sm font-medium text-gray-700 mb-1'
                    ]
                ],
                'invalid_message' => 'Les champs de mot de passe doivent correspondre.',
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
