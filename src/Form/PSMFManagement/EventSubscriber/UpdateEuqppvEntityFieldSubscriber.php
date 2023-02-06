<?php

namespace App\Form\PSMFManagement\EventSubscriber;

use App\Entity\LovManagement\WorkRole;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class UpdateEuqppvEntityFieldSubscriber implements EventSubscriberInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return [FormEvents::POST_SUBMIT => 'onEuqppvEntityChanged'];
    }

    public function onEuqppvEntityChanged(FormEvent $event): void
    {
        $form = $event->getForm();

        if ($form->getData()) {
            $euqppvEntity = $form->getData();

            // workRoles
            $euQPPV = $this->em->getRepository("App\Entity\LovManagement\WorkRole")->findOneBy(['code'=>WorkRole::EUQPPV]);
            $deputyEUQPPV = $this->em->getRepository("App\Entity\LovManagement\WorkRole")->findOneBy(['code'=>WorkRole::DEPUTY_EUQPPV]);
            //$contactPvClient = $this->em->getRepository("App\Entity\LovManagement\WorkRole")->findOneBy(['code'=>WorkRole::CONTACT_PV_CLIENT]);

            $form->getParent()->add('euQPPV', EntityType::class, [
                        'label' => 'psmf.euQPPV',
                        'class' => 'App\Entity\UserManagement\User',
                        'query_builder' => function (EntityRepository $er) use ($euqppvEntity, $euQPPV) {
                            return $er->createQueryBuilder('u')
                                ->join('u.workRoles', 'wr')
                                ->where('u.isEnable = true AND u.pvUser = true AND u.workAttachment = :workAttachment AND wr.id = :workRole')
                                ->setParameters([
                                    'workAttachment' => $euqppvEntity,
                                    'workRole' => $euQPPV->getId()
                                ])
                                ->orderBy('u.firstName', 'DESC');
                        },
                        'attr' => ['class' => 'chosen'],
                        'required' => true,
                        //'help' => 'SYS_EUQPPV_XX'
                    ])
                    ->add('deputyEUQPPV', EntityType::class, [
                        'label' => 'psmf.deputyEUQPPV',
                        'class' => 'App\Entity\UserManagement\User',
                        'query_builder' => function (EntityRepository $er) use ($euqppvEntity, $deputyEUQPPV) {
                            return $er->createQueryBuilder('u')
                                ->join('u.workRoles', 'wr')
                                ->where('u.isEnable = true AND u.pvUser = true AND u.workAttachment = :workAttachment AND wr.id = :workRole')
                                ->setParameters([
                                    'workAttachment' => $euqppvEntity,
                                    'workRole' => $deputyEUQPPV->getId()
                                ])                        
                                ->orderBy('u.firstName', 'DESC');
                        },
                        'attr' => ['class' => 'chosen'],
                        'required' => false,
                        //'help' => 'SYS_DEPUTY_EUQPPV_XX'
                    ])          
                    // ->add('contactPvClient', EntityType::class, [
                    //     'label' => 'psmf.contactPvClient',
                    //     'class' => 'App\Entity\UserManagement\User',
                    //     'query_builder' => function (EntityRepository $er) use ($euqppvEntity, $contactPvClient) {
                    //         return $er->createQueryBuilder('u')
                    //             ->join('u.workRoles', 'wr')
                    //             ->where('u.isEnable = true AND u.pvUser = true AND u.workAttachment = :workAttachment AND wr.id = :workRole')
                    //             ->setParameters([
                    //                 'workAttachment' => $euqppvEntity,
                    //                 'workRole' => $contactPvClient->getId()
                    //             ])
                    //             ->orderBy('u.firstName', 'DESC');
                    //     },
                    //     'attr' => ['class' => 'chosen'],
                    //     'required' => true,
                    // ]) 
                ; 
        }
    }
}
