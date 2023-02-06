<?php

namespace App\Form\TemplateManagement;

use App\Entity\TemplateManagement\CorrespondanceGlobaleHistory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CorrespondanceGlobaleHistoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add('reason')
            ->add('correspondances', CollectionType::class, [
                'entry_type' => Correspondance3Type::class,
                'prototype'         => true,
                'allow_add'         => false,
                'allow_delete'      => false,
                'required'          => true,
                'label'             => false,
           ])
            ->add('isMajeur', CheckboxType::class, [
                    'label' => 'section.isMajeur',
                    'required' => false,
            ])            
            ->add('reason', null, [
                'label' => 'section.reason',
                'label_attr' => [
                    'class' => 'required'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'rows' => '5',
                ],                
                'data' => '',
                'required' => false,
                'trim' => true,
            ])  
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CorrespondanceGlobaleHistory::class,
        ]);
    }
}
