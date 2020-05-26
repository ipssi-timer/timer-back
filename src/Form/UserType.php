<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      if( empty($options['data']->getId()) ) {

        $builder
          ->add('pseudo',TextType::class)
          ->add('firstName', TextType::class)
          ->add('lastName', TextType::class)
          ->add('birthDate', DateType::class)
          ->add('email', EmailType::class)
          ->add('password', RepeatedType::class,
            [
              'type' => PasswordType::class,
              'first_options' => ['label' => 'password'],
              'second_options' => ['label' => "confirm password"]
            ])
          ->add('roles', ChoiceType::class, [
            'choices' => [
              'Gestion' => [
                'Admin' => 'admin',
                'user ' => 'user',

              ],
            ],
          ])
          ->add('signUp', SubmitType::class);
      }else{
        $builder
          ->add('pseudo',TextType::class)
          ->add('firstName', TextType::class)
          ->add('lastName', TextType::class)
          ->add('birthDate', DateType::class)
          ->add('email', EmailType::class)
          ->add('password', RepeatedType::class,
            [
              'type' => PasswordType::class,
              'first_options' => ['label' => 'password'],
              'second_options' => ['label' => "confirm password"]
            ])

          ->add('signUp', SubmitType::class);
      }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
      /*  $resolver->setDefaults([
            'data_class' => User::class,
        ]);*/
    }
}
