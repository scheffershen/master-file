<?php

namespace App\Form\TemplateManagement;

use App\Form\TemplateManagement\EventSubscriber\VariablePreSetDataSubscriber;
use App\Validator\TemplateManagement\UniqueBalise;
use Symfony\Component\Form\FormBuilderInterface;

class VariableEditType extends VariableType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('balise', null, [
                'label' => 'variable.balise',
                'required' => true,
                'trim' => true,
                'constraints' => [
                        new UniqueBalise(),
                ],            
                'help' => 'La balise ne doit être composée que de majuscules, de chiffres et de traits de soulignement, ex: OPTION_XXX, DATE_XXX, IMG_XXX, INT_XXX, TEXT_XXX, LONGTEXT_XXX, AUTRE_XXX',
            ]) 
            ->addEventSubscriber(new VariablePreSetDataSubscriber()); 
    }

}
