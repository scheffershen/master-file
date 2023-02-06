<?php

namespace App\Form\LovManagement;

//form use stetment
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

//autre use statement

class LovType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', TextType::class, [
                    'required' => true, ])
                ->add('sort', NumberType::class, [
                    'required' => false, ])
                ;
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix()
    {
        return 'umds_lovbundle_lovtype';
    }
}
