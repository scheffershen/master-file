<?php

namespace App\Form\PSMFManagement\EventSubscriber;

use App\Entity\LovManagement\LocalQPPVPays;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class AddLocalQPPVPaysFieldSubscriber implements EventSubscriberInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return [FormEvents::PRE_SET_DATA => 'onLocalQPPVPaysSelected'];
    }

    public function onLocalQPPVPaysSelected(FormEvent $event): void
    {
        $form = $event->getForm();
        $data = $event->getData();

        $localQPPVPays = $data->getLocalQPPVPays();

        if ($localQPPVPays) {
            $form->add('localQPPVUM', EntityType::class, [
                'label' => 'psmf.localQPPVUM',
                'class' => 'App\Entity\LovManagement\LocalQPPV',
                'query_builder' => static function (EntityRepository $er) use ($localQPPVPays) {
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
