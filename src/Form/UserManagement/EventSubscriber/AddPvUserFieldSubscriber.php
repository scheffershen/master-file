<?php

namespace App\Form\UserManagement\EventSubscriber;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class AddPvUserFieldSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [FormEvents::PRE_SET_DATA => 'onPvUserSelected'];
    }

    public function onPvUserSelected(FormEvent $event): void
    {
        $form = $event->getForm();
        $data = $event->getData();
        $pvUser = $data->getPvUser();

        if ($pvUser) {
            $form->add('gender', null, [
                    'label' => 'lov.gender',
                    'attr' => ['class' => 'pvUser'],
                    'required' => true,
                ]) 
                ->add('workRoles', EntityType::class, [
                    'label' => 'lov.workRole',
                    'class' => 'App\Entity\LovManagement\WorkRole',
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('l')
                            ->where('l.isValid = true')
                            ->orderBy('l.sort', 'DESC');
                    },
                    'attr' => ['class' => 'chosen'],
                    'multiple' => true,
                    'required' => true,
                    'help' => 'EUQPPV, Deputy EUQPPV, Contact PV client'
                ])
                ->add('workFunction', null, [
                    'label' => 'user.workFunction',
                    'attr' => ['class' => 'pvUser'],
                    'required' => true,
                ])         
                ->add('mobile', null, [
                    'label' => 'label.mobile',
                    //'attr' => ['class' => 'pvUser'],
                    'required' => false,
                ])
                ->add('fixe', null, [
                    'label' => 'label.fixe',
                    'attr' => ['class' => 'pvUser'],
                    'required' => true,
                ])
                ->add('fax', null, [
                    'label' => 'label.fax',
                    'attr' => ['class' => 'pvUser'],
                    'required' => true,
                ])            
                ->add('workAttachment', null, [
                    'label' => 'lov.rattachement',
                    'attr' => ['class' => 'pvUser'],
                    'required' => true
                ])
                ->add('workName', null, [
                    'label' => 'user.workName',
                    'attr' => ['class' => 'pvUser'],
                    'required' => true,
                ])   
                ->add('adresse', null, [
                    'label' => 'label.adresse',
                    'attr' => ['class' => 'pvUser'],
                    'required' => true,
                ])                                        
            ; 
        }
    }
}
