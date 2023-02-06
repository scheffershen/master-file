<?php

namespace App\Form\TemplateManagement;

use Symfony\Component\Form\FormBuilderInterface;

class ClasseShowType extends ClasseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->remove('title')
            ->remove('sections')            
            ->remove('variables')
            // ->add('reason', null, [
            //     'label' => 'section.reason',
            //     'attr' => [
            //         'class' => 'form-control',
            //         'rows' => '5',
            //     ],                
            //     'data' => '',
            //     'required' => true,
            //     'trim' => true,
            // ])
            //->remove('isAnnexe')                          
        ;
    }
}
