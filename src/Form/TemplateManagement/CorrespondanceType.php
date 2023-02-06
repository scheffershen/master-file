<?php

namespace App\Form\TemplateManagement;

use App\Entity\LovManagement\Obligation;
use App\Entity\LovManagement\TypeVariable; 
use App\Entity\TemplateManagement\Correspondance;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CorrespondanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $variable = $options['variable'];
        // (text, integer, Option, Autre, Date),  (Image), (TextLong)
        switch ($variable->getType()->getCode()) {
            case TypeVariable::IMAGE:
                $builder
                    ->add('upload', FileType::class, [
                            'label' => 'correspondance.valueLocalImage',
                            'mapped' => false,                        
                            'required' => false,
                        ])
                ;  
                break;
            case TypeVariable::TEXT_LONG:
                $builder
                    ->add('valueLocal', null, [
                        'attr' => [
                            'class' => 'form-control', // summer-note
                            'rows' => '5',
                        ],
                        'label' => 'correspondance.valueLocal',
                        'required' => true,
                        ////'purify_html' => true
                    ])
                ;                
                break;
            case TypeVariable::OPTION:
                $builder
                    ->add('valueLocal', ChoiceType::class, [
                        'label' => 'correspondance.valueLocal',
                        'choices'  => array(
                            'Client'  => 'Client',
                            'UM' => 'UM',
                            'Les deux' => 'Les deux'
                            ),
                        'expanded' => false,
                        'multiple' => false, 
                    ])
                ;                
                break;                 
            default:
                $builder
                    ->add('valueLocal', TextType::class, [
                        'label' => 'correspondance.valueLocal',
                        'required' => true,
                    ])
                ;
                break;  
        }

        $builder->add('reason', TextAreaType::class, [
            'label' => 'section.reason',
            'attr' => [
                'class' => 'form-control',
                'rows' => '5',
            ],                
            'data' => '',
            'required' => true,
            'trim' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => [
                'data_class' => Correspondance::class,
                'allow_extra_fields' => true
            ],
        ])
        ->setRequired('variable')
        ;        
    }
}
