<?php

namespace App\Form\TemplateManagement;

use Symfony\Component\Form\FormBuilderInterface;

class SectionShowType extends SectionType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->remove('title')        
            ->remove('position')
            ->remove('contenu')        
            ->remove('isValid')
            ->remove('parent')
            ->remove('isAnnexe')
            ->remove('allowSubSection')
            ->add('reason', null, [
                'label' => 'section.reason',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => '5',
                ],                 
                'data' => '',
                'required' => true,
                'trim' => true,
            ])              
        ;
    }

}
