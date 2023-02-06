<?php

namespace App\Form\UserManagement;

use App\Form\UserManagement\EventSubscriber\AddPvUserFieldSubscriber;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;

class UserEditType extends UserType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        $builder 
            ->add('cv', FileType::class, [
                'required' => false,
                'mapped' => false,
                'help' => 'Image in format .jpg, .png and .gif' 
            ])  // cas il y de problème de chagement de l'ancienne image avec VichUpdload    
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

        $builder->addEventSubscriber(new AddPvUserFieldSubscriber());    
    }

}
