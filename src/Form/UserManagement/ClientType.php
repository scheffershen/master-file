<?php

namespace App\Form\UserManagement;

use App\Entity\UserManagement\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('adress')
            ->add('logo', VichFileType::class, [
                'required' => false,
                'allow_delete' => true,
                'download_uri' => false,
                'download_label' => false
            ])
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}
