<?php

namespace App\Form\PSMFManagement\EventSubscriber;

use App\Entity\LovManagement\WorkRole;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Security;

class AddClientFieldSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $em;
    private Security $security;

    public function __construct(EntityManagerInterface $em, Security $security)
    {
        $this->em = $em;
        $this->security = $security;
    }

    public static function getSubscribedEvents()
    {
        return [FormEvents::PRE_SET_DATA => 'onClientField'];
    }

    public function onClientField(FormEvent $event): void
    {
        $form = $event->getForm();
        // $data = $event->getData();
        // $client = $data->getContactPvClient();

        $contactPvClient = $this->em->getRepository("App\Entity\LovManagement\WorkRole")->findOneBy(['code'=>WorkRole::CONTACT_PV_CLIENT]);
        
        $user = $this->security->getUser();

        if (!$this->security->isGranted('ROLE_ADMIN')) {
            // client filtre pour role_utilisateur
            $form->add('client', EntityType::class, [
                'label' => 'psmf.client',
                'class' => 'App\Entity\UserManagement\Client',
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) use ($user)  {
                    return $er->createQueryBuilder('c')
                        ->where('c.isValid = true')
                        ->andWhere('c.id IN (:client)')
                        ->setParameter('client', $user->getClients())
                        ->orderBy('c.name', 'DESC');
                },
                'attr' => ['class' => 'chosen'],
                'required' => true,
                //'help' => 'SYS_CLIENT_XX'
            ])
            ->add('contactPvClient', EntityType::class, [
                'label' => 'psmf.contactPvClient',
                'class' => 'App\Entity\UserManagement\User',
                'query_builder' => function (EntityRepository $er) use ($contactPvClient, $user) {
                    return $er->createQueryBuilder('u')
                        ->join('u.clients', 'c')
                        ->join('u.workRoles', 'wr')
                        ->where('c.isValid = true AND c.id IN (:client) AND u.isEnable = true AND u.pvUser = true AND wr.id = :workRole')
                        ->setParameters([
                            'client' => $user->getClients(),
                            'workRole' => $contactPvClient->getId()
                        ])
                        ->orderBy('u.firstName', 'DESC');
                },
                'attr' => ['class' => 'chosen'],
                'required' => true,
                //'choices' => [],
                //'help' => 'SYS_CONTACT_PV_CLIENT_XX'
            ])             
            ; 
        }
    }
}
