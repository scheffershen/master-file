<?php

namespace App\Form\TemplateManagement;

use App\Entity\TemplateManagement\Section;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, [
                'label' => 'section.title',
                'required' => true,
                'trim' => true,
            ])        
            ->add('position', null, [
                'required' => true,
            ])
            ->add('contenu', null, [
                'attr' => [
                    'class' => 'form-control', // summer-note
                    'rows' => '15',
                ],
                'label' => 'section.contenu',
                'required' => false,
                'trim' => true,
                //'purify_html' => true
            ])        
            ->add('isValid', CheckboxType::class, [
                    'label' => 'section.isValid',
                    'required' => false,
                ])
            ->add('parent', EntityType::class, [
                'label' => 'section.parent',
                'class' => 'App\Entity\TemplateManagement\Section',
                'choice_label' => 'title',
                'query_builder' => function (EntityRepository $er)  {
                    return $er->createQueryBuilder('s')
                        ->where('s.isValid = true')
                        ->andWhere('s.allowSubSection = true')
                        ->orderBy('s.title', 'ASC');
                },
                'placeholder' => 'Global',
                'required' => false,
            ])
            ->add('isAnnexe', CheckboxType::class, [
                    'label' => 'section.isAnnexe',
                    'required' => false,
                ])   
                //allowSubSection 
            ->add('allowSubSection', CheckboxType::class, [
                    'label' => 'section.allowSubSection',
                    'required' => false,
                ])   
            ->add('isPageBreak', CheckboxType::class, [
                    'label' => 'section.isPageBreak',
                    'required' => false,
                ])    
            ->add('isMajeur', CheckboxType::class, [
                    'label' => 'section.isMajeur',
                    'required' => false,
            ])                                                                   
            ->add('reason', null, [
                'label' => 'section.reason',
                'label_attr' => [
                    'class' => 'required'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'rows' => '5',
                ],                
                'data' => '',
                'required' => false,
                'trim' => true,
            ])         

        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Section::class,
        ]);
    }
}
