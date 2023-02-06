<?php

namespace App\Form\PSMFManagement\EventSubscriber;

use App\Entity\LovManagement\WorkRole;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class UpdateClientFieldSubscriber implements EventSubscriberInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return [FormEvents::POST_SUBMIT => 'onClientChanged'];
    }

    public function onClientChanged(FormEvent $event): void
    {
        $form = $event->getForm();

        if ($form->getData()) {
            $client = $form->getData();

            $contactPvClient = $this->em->getRepository("App\Entity\LovManagement\WorkRole")->findOneBy(['code'=>WorkRole::CONTACT_PV_CLIENT]);

            $form->getParent()->add('contactPvClient', EntityType::class, [
                        'label' => 'psmf.contactPvClient',
                        'class' => 'App\Entity\UserManagement\User',
                        'query_builder' => function (EntityRepository $er) use ($client, $contactPvClient) {
                            return $er->createQueryBuilder('u')
                                ->join('u.workRoles', 'wr')
                                ->join('u.clients', 'c')
                                ->where('u.isEnable = true AND u.pvUser = true AND c.id = :client AND wr.id = :workRole')
                                ->setParameters([
                                    'client' => $client->getId(),
                                    'workRole' => $contactPvClient->getId()
                                ])
                                ->orderBy('u.firstName', 'DESC');
                        },
                        'attr' => ['class' => 'chosen'],
                        'required' => true,
                        //'help' => 'SYS_CONTACT_PV_CLIENT_XX'
                    ]) 
                ; 
        }
    }
}
