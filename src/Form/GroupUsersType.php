<?php

namespace App\Form;

use App\Entity\GroupUsers;
use App\Entity\User;
use Doctrine\DBAL\Types\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupUsersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      if(empty($options['data']->getId())){
        $builder
            ->add('name')
            ->add('users',EntityType::class,[
              'class'=>User::class,
              'multiple'=>true,
              'choice_label'=> function($user){
                  return $user->getPseudo();
              }
            ])
          ->add('submit',SubmitType::class)
        ;}else{
        $builder
          ->add('name')
          ->add('users',EntityType::class,[
            'class'=>User::class,
            'multiple'=>true,
            'choice_label'=> function($user){
              return $user->getPseudo();
            }
          ])
          ->add('creator_id')
          ->add('submit',SubmitType::class)
        ;

      }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => GroupUsers::class,
        ]);
    }
}
