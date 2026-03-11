<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'E-posta Adresi'
            ])
            ->add('fullName', TextType::class, [
                'label' => 'Ad Soyad'
            ])
            ->add('phone', TextType::class, [
                'label' => 'Telefon Numarası'
            ])
            ->add('birthYear', IntegerType::class, [
                'label' => 'Doğum Yılı'
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'label' => 'Şifre',
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Lütfen bir şifre girin.',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Şifreniz en az {{ limit }} karakter olmalıdır.',
                        'max' => 4096,
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
