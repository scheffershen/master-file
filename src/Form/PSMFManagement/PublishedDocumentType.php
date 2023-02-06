<?php

namespace App\Form\PSMFManagement;

use App\Entity\PSMFManagement\PublishedDocument;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PublishedDocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('comentary', null, [
                        'attr' => [
                            'class' => 'form-control',
                            'rows' => '5',
                        ],
                        'label' => 'document.comentary',
                        'required' => true,
                    ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PublishedDocument::class,
        ]);
    }
}
