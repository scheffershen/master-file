<?php

namespace App\Form\UserManagement;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;

class ClientEditType extends ClientType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('logo', FileType::class, [
                'required' => false,
                'mapped' => false, 
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
}
