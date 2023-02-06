<?php

namespace App\Form\UserManagement;

use Symfony\Component\Form\FormBuilderInterface;

class UserDeleteType extends UserType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        $builder
            ->remove('username')
            ->remove('email')
            ->remove('firstName')
            ->remove('lastName')
            ->remove('roles')
            ->remove('isEnable')
            ->remove('clients')
            ->remove('mailAlerte')
            ->remove('pvUser')
            ->remove('gender')
            ->remove('cv')
            ->remove('workRoles')  
            ->remove('workFunction')  
            ->remove('mobile')  
            ->remove('fixe')  
            ->remove('fax')  
            ->remove('workAttachment')  
            ->remove('workName') 
            ->remove('adresse')   
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
            ;
    }

}
