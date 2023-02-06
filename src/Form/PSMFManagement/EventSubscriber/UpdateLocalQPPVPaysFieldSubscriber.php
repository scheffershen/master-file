<?php

namespace App\Form\PSMFManagement\EventSubscriber;

use App\Entity\LovManagement\LocalQPPVPays;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class UpdateLocalQPPVPaysFieldSubscriber implements EventSubscriberInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return [FormEvents::POST_SUBMIT => 'onLocalQPPVPaysChanged'];
    }

    public function onLocalQPPVPaysChanged(FormEvent $event): void
    {
        $form = $event->getForm();

        if ($form->getData()) {
            $localQPPVPays = $form->getData();

            $form->getParent()->add('localQPPVUM', EntityType::class, [
                'label' => 'psmf.localQPPVUM',
                'class' => 'App\Entity\LovManagement\LocalQPPV',
                'query_builder' => static function (EntityRepository $er) use ($localQPPVPays)  {
                    return $er->createQueryBuilder('l')
                        ->where('l.id IN (:localQPPVPays)')
                        ->setParameter('localQPPVPays', $localQPPVPays);
                },
                'attr' => ['class' => 'chosen'],
                'multiple' => true,
                'required' => false,
                //'help' => '_if_LOCAL_QPPV_AUSTRIA_UM_ et _if_LOCAL_QPPV_AUSTRIA_OTHER_ ... _endif_'
            ]) 
            ; 
        }
    }
}
