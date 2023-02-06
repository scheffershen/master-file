<?php

namespace App\Form\UserManagement;

use Symfony\Component\Form\FormBuilderInterface;

class ClientDeleteType extends ClientType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->remove('name')
            ->remove('adress')        
            ->remove('logo')  
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
