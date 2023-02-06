<?php

namespace App\Form\TemplateManagement;

use Symfony\Component\Form\FormBuilderInterface;

class VariableShowType extends VariableType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->remove('label')
            ->remove('balise')            
            ->remove('obligation')
            ->remove('userHelp')             
            ->remove('type')
            ->remove('scope')
            ->remove('classe')
            ->remove('description')
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
