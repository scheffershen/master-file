<?php

namespace App\Form\TemplateManagement\EventSubscriber;

use App\Entity\LovManagement\Scope;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class VariablePreSetDataSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [FormEvents::PRE_SET_DATA => 'onPreSetData'];
    }

    public function onPreSetData(FormEvent $event): void
    {
        $form = $event->getForm();
        $variable = $event->getData();

        if (Scope::SYETEME === $variable->getScope()->getCode() )
        {
            $form->add('scope', null, [
                'label' => 'lov.scope',
                'class' => 'App\Entity\LovManagement\Scope',
                'choice_label' => 'title',
                'query_builder' => function (EntityRepository $er)  {
                    return $er->createQueryBuilder('s')
                        ->where('s.isValid = true')
                        ->andWhere('s.code = :systeme')
                        ->setParameter('systeme', Scope::SYETEME) 
                        ->orderBy('s.sort', 'ASC');
                },
                'required' => true,
            ])
            ;
        }
    }
}
