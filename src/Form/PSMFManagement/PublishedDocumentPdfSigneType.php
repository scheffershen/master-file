<?php

namespace App\Form\PSMFManagement;

use App\Entity\PSMFManagement\PublishedDocument;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class PublishedDocumentPdfSigneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pdfSigne', FileType::class, [
                    'label' => 'document.importer',
                    'required' => false,
                    'mapped' => false,
                    'attr' => [
                        'class' => 'form-control check-mime ',
                        'data-mime' => 'application/pdf',
                    ],
                    'help' => 'Format .pdf',                    
            ])        
            ->add('pdfSigneComentary', null, [
                    'attr' => [
                        'class' => 'form-control',
                        'rows' => '5',
                    ],
                    'label' => 'document.comentary',
                    'data' => '',
                    'required' => true,
                    'trim' => true,
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
