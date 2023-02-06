<?php

namespace App\Form\TemplateManagement;

use App\Entity\LovManagement\Scope;
use App\Entity\TemplateManagement\Variable;
use App\Validator\TemplateManagement\UniqueBalise;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VariableType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('label')
            ->add('balise', null, [
                'label' => 'variable.balise',
                'required' => true,
                'trim' => true,
                'constraints' => [
                        new UniqueBalise(),
                ],               
                'help' => 'La balise ne doit être composée que de majuscules, de chiffres et de traits de soulignement, ex: OPTION_XXX, DATE_XXX, IMG_XXX, INT_XXX, TEXT_XXX, LONGTEXT_XXX, AUTRE_XXX',
            ])             
            ->add('obligation')
            ->add('userHelp', null, [
                'attr' => [
                    'class' => 'form-control', // summer-note
                    'rows' => '5',
                ],
                'label' => 'label.userHelp',
                'required' => true,
                //'purify_html' => true
            ])             
            ->add('type', EntityType::class, [
                'label' => 'lov.typeVariable',
                'class' => 'App\Entity\LovManagement\TypeVariable',
                'choice_label' => 'title',
                'query_builder' => function (EntityRepository $er)  {
                    return $er->createQueryBuilder('t')
                        ->where('t.isValid = true')
                        ->orderBy('t.sort', 'ASC');
                },
                'required' => true,
            ])
            ->add('scope', null, [
                'label' => 'lov.scope',
                'class' => 'App\Entity\LovManagement\Scope',
                'choice_label' => 'title',
                'query_builder' => function (EntityRepository $er)  {
                    return $er->createQueryBuilder('s')
                        ->where('s.isValid = true')
                        ->andWhere('s.code != :systeme')
                        ->setParameter('systeme',Scope::SYETEME) 
                        ->orderBy('s.sort', 'ASC');
                },
                'required' => true,
            ])
            ->add('classes', EntityType::class, [
                'label' => 'classe.label',
                'choice_label' => 'title',
                'class' => 'App\Entity\TemplateManagement\Classe',
                'query_builder' => function (EntityRepository $er)  {
                    return $er->createQueryBuilder('c')
                        ->where('c.isValid = true')
                        ->andWhere('c.isDeleted = false')
                        ->orderBy('c.title', 'ASC');
                },
                'attr' => [
                    'class' => 'js-select2'
                ],
                'multiple' => true,
                'required' => false,
            ])            
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
            // ->add('isAnnexe', CheckboxType::class, [
            //         'label' => 'section.isAnnexe',
            //         'required' => false,
            //     ])  
            ->add('description', null, [
                'attr' => [
                    'class' => 'form-control',
                    'rows' => '5',
                ],
                'label' => 'label.description',
                'required' => false,
            ])                                         
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Variable::class,
        ]);
    }
}
