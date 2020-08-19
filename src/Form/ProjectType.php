<?php

namespace App\Form;

use App\Entity\GroupUsers;
use App\Entity\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('projectgroup',EntityType::class,[
                'class'=>GroupUsers::class,
                'multiple'=>false,
                'choice_label'=> function($group){
                    return $group->getName();
                }
            ])
            ->add('creator_id')
            ->add('submit',SubmitType::class)
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
