<?php

namespace App\Form\TemplateManagement;

use App\Entity\TemplateManagement\Classe;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClasseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            //->add('code')
            ->add('sections', EntityType::class, [
                'label' => 'sections',
                'class' => 'App\Entity\TemplateManagement\Section',
                'query_builder' => static function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->where('s.isValid = true')
                        ->orderBy('s.position', 'ASC');
                },
                'attr' => [
                    'class' => 'js-select2'
                ],
                'multiple' => true,
                'required' => false,
            ]) 
            ->add('variables', EntityType::class, [
                'label' => 'variables',
                'class' => 'App\Entity\TemplateManagement\Variable',
                'query_builder' => static function (EntityRepository $er) {
                    return $er->createQueryBuilder('v')
                        ->where('v.isValid = true')
                        ->orderBy('v.balise', 'ASC');
                },
                'attr' => [
                    'class' => 'js-select2'
                ],
                'multiple' => true,
                'required' => false,
            ])                         
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Classe::class,
        ]);
    }
}
