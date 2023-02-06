<?php

namespace App\Form\TemplateManagement;

use App\Entity\LovManagement\Obligation;
use App\Entity\LovManagement\TypeVariable; 
use App\Entity\TemplateManagement\Correspondance;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class Correspondance3Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $bilanForm = $event->getForm();
            $correspondance = $event->getData();
            $this->formAdd($bilanForm, $correspondance);
        });
    }

    public function formAdd(Form $bilanForm, Correspondance $correspondance) {
        if ($correspondance->getVariable()->getObligation()->getCode() == Obligation::OBLIGATOIRE) {
            $required = true;
        } else {
            $required = false;
        }        
        switch ($correspondance->getVariable()->getType()->getCode()) {
            case TypeVariable::IMAGE:
                if ($correspondance->getValueLocal()) {
                    $bilanForm
                        ->add('upload', FileType::class, [
                            'label' => 'correspondance.valueLocalImage',
                            'mapped' => false,                        
                            'required' => false,
                        ])
                    ;    
                } else {
                    $bilanForm
                        ->add('upload', FileType::class, [
                            'label' => 'correspondance.valueLocalImage',
                            'mapped' => false,                        
                            'required' => false, //$required,
                        ])
                    ;               
                }
                break;
            case TypeVariable::TEXT_LONG:
                $bilanForm
                    ->add('valueLocal', null, [
                        'attr' => [
                            'class' => 'form-control', // summer-note
                            'rows' => '5',
                        ],
                        'label' => 'correspondance.valueLocal',
                        'required' => false, //$required,
                        //'purify_html' => true
                    ])
                ;                
                break;
            case TypeVariable::OPTION:
                $bilanForm
                    ->add('valueLocal', ChoiceType::class, [
                        'label' => 'correspondance.valueLocal',
                        'choices'  => array(
                            'Client'  => 'Client',
                            'UM' => 'UM',
                            'Les deux' => 'Les deux'
                            ),
                        'expanded' => false,
                        'multiple' => false,
                        'required' => false, //$required,
                    ])
                ;                
                break;                 
            default:
                $bilanForm
                    ->add('valueLocal', TextType::class, [
                        'label' => 'correspondance.valueLocal',
                        'required' =>  false, //$required,
                    ])
                ;
                break;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Correspondance::class,
        ])
        ;        
    }
}
