<?php

namespace App\Form\TemplateManagement\EventSubscriber;

use App\Entity\TemplateManagement\Section; 
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class SectionPreSetDataSubscriber implements EventSubscriberInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return [FormEvents::PRE_SET_DATA => 'onPreSetData'];
    }

    public function onPreSetData(FormEvent $event): void
    {
        $form = $event->getForm();
        $section = $event->getData();

        $position_disabled = $parent_disabled =  $isAnnexe_disabled =  $isValid_disabled = $allowSubSection_disabled = $isPageBreak_disabled = false; 

        if (in_array($section->getId(), [Section::HEADER_ID, Section::FOOTER_ID, Section::PV_SYSTEM_MASTER_ID, Section::TABLE_CONTENTS_ID, Section::ABBREVIATIONS_ID])) {
            $position_disabled = $parent_disabled =  $isAnnexe_disabled =  $isValid_disabled = $allowSubSection_disabled = true; 
        }

        if (!$section->getParent()) $isPageBreak_disabled = true;
        
        if (false === $section->getContenuEditable()) { 
            $form->remove('contenu');
        } 

        $form->add('position', null, [
                'label' => 'section.position',
                'required' => true,
                'disabled' => $position_disabled,
            ])
            ->add('isValid', CheckboxType::class, [
                    'label' => 'section.isValid',
                    'required' => false,
                    'disabled' => $isValid_disabled,
                ])            
            ->add('parent', EntityType::class, [
                'label' => 'section.parent',
                'class' => 'App\Entity\TemplateManagement\Section',
                'choice_label' => 'title',
                'query_builder' => function (EntityRepository $er)  {
                    return $er->createQueryBuilder('s')
                        ->where('s.isValid = true')
                        ->andWhere('s.allowSubSection = true')
                        ->orderBy('s.position', 'ASC');
                },
                'placeholder' => 'Global',
                'required' => false,
                'disabled' => $parent_disabled,
            ])  
            ->add('isAnnexe', CheckboxType::class, [
                    'label' => 'section.isAnnexe',
                    'required' => false,
                    'disabled' => $isAnnexe_disabled,
            ])  
            ->add('allowSubSection', CheckboxType::class, [
                    'label' => 'section.allowSubSection',
                    'required' => false,
                    'disabled' => $allowSubSection_disabled,
            ])      
            ->add('isPageBreak', CheckboxType::class, [
                    'label' => 'section.isPageBreak',
                    'required' => false,
                    'disabled' => $isPageBreak_disabled,
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

        // ne afficher pas lui-meme et ses sous-sections, yishen at 18/01/2022
        if ($section->getId()) {
            $parents = $this->em->getRepository("App\Entity\TemplateManagement\Section")->findSubSections($section);
            //dump($parents);
            // ses sous-sections
            $form->add('parent', EntityType::class, [
                    'label' => 'section.parent',
                    'class' => 'App\Entity\TemplateManagement\Section',
                    'choice_label' => 'title',
                    'query_builder' => function (EntityRepository $er) use ($parents)  {
                        return $er->createQueryBuilder('s')
                            ->where('s.isValid = true')
                            ->andWhere('s.allowSubSection = true')
                            ->andWhere('s.id NOT IN (:parents)')
                            ->setParameters([
                                'parents' => $parents
                            ])                            
                            ->orderBy('s.title', 'ASC');
                    },
                    'placeholder' => 'Global',
                    'required' => false,
                ]); 
        }

    }
}
