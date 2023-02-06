<?php

namespace App\Form\PSMFManagement;

use App\Entity\LovManagement\WorkRole;
use App\Entity\PSMFManagement\PSMF;
use App\Form\PSMFManagement\EventSubscriber\AddClientFieldSubscriber;
use App\Form\PSMFManagement\EventSubscriber\AddEuqppvEntityFieldSubscriber;
use App\Form\PSMFManagement\EventSubscriber\AddLocalQPPVPaysFieldSubscriber;
use App\Form\PSMFManagement\EventSubscriber\UpdateClientFieldSubscriber;
use App\Form\PSMFManagement\EventSubscriber\UpdateEuqppvEntityFieldSubscriber;
use App\Form\PSMFManagement\EventSubscriber\UpdateLocalQPPVPaysFieldSubscriber;
use App\Validator\PSMFManagement\LocalQPPVUMUnique;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class PSMFType extends AbstractType
{
    private EntityManagerInterface $em;
    private Security $security;

    public function __construct(EntityManagerInterface $em, Security $security)
    {
        $this->em = $em;
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // workRoles
        $euQPPV = $this->em->getRepository("App\Entity\LovManagement\WorkRole")->findOneBy(['code'=>WorkRole::EUQPPV]);
        $deputyEUQPPV = $this->em->getRepository("App\Entity\LovManagement\WorkRole")->findOneBy(['code'=>WorkRole::DEPUTY_EUQPPV]);        
        $contactPvClient = $this->em->getRepository("App\Entity\LovManagement\WorkRole")->findOneBy(['code'=>WorkRole::CONTACT_PV_CLIENT]);

        $builder
            ->add('title', null, [
                'label' => 'psmf.title',
                'label_attr' =>  ['id' => 'psmf_title'],
                'required' => true,
                //'help' => 'SYS_PSMF_TITRE'
            ])
            ->add('client', EntityType::class, [
                'label' => 'psmf.client',
                'label_attr' =>  ['id' => 'psmf_client'],
                'class' => 'App\Entity\UserManagement\Client',
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er)  {
                    return $er->createQueryBuilder('c')
                        ->where('c.isValid = true')
                        ->orderBy('c.name', 'DESC');
                },
                'attr' => ['class' => 'chosen'],
                'required' => true,
                //'help' => 'SYS_CLIENT_XX'
            ])
            ->add('euqppvEntity', EntityType::class, [
                'label' => 'psmf.euqppvEntity',
                'label_attr' =>  ['id' => 'psmf_euqppvEntity'],
                'class' => 'App\Entity\LovManagement\EntitType',
                'choice_label' => 'title',
                'query_builder' => function (EntityRepository $er)  {
                    return $er->createQueryBuilder('e')
                        ->where('e.isValid = true')
                        ->orderBy('e.title', 'DESC');
                },
                'attr' => ['class' => 'chosen'],
                'required' => true,
                //'help' => '_if_um_euqppv_ et _if_presta_euqppv_ et _if_client_euqppv_ ... _endif_'
            ])
            ->add('eudravigNum', null, [
                'label' => 'psmf.eudravigNum',
                'label_attr' =>  ['id' => 'psmf_eudravigNum'],
                'required' => false,
                //'help' => 'SYS_PSMF_EUDRA_NUMBER'
            ])
            ->add('euQPPV', EntityType::class, [
                'label' => 'psmf.euQPPV',
                'label_attr' =>  ['id' => 'psmf_euQPPV'],
                'class' => 'App\Entity\UserManagement\User',
                'query_builder' => function (EntityRepository $er) use ( $euQPPV) {
                    return $er->createQueryBuilder('u')
                        ->join('u.workRoles', 'wr')
                        ->where('u.isEnable = true AND u.pvUser = true AND wr.id = :workRole')
                        ->setParameters([
                            'workRole' => $euQPPV->getId()
                        ])
                        ->orderBy('u.firstName', 'DESC');
                },
                'attr' => ['class' => 'chosen'],
                'required' => true,
                //'choices' => [],
            ])
            ->add('deputyEUQPPV', EntityType::class, [
                'label' => 'psmf.deputyEUQPPV',
                'label_attr' =>  ['id' => 'psmf_deputyEUQPPV'],
                'class' => 'App\Entity\UserManagement\User',
                'query_builder' => function (EntityRepository $er) use ($deputyEUQPPV) {
                    return $er->createQueryBuilder('u')
                        ->join('u.workRoles', 'wr')
                        ->where('u.isEnable = true AND u.pvUser = true AND wr.id = :workRole')
                        ->setParameters([
                            'workRole' => $deputyEUQPPV->getId()
                        ])                        
                        ->orderBy('u.firstName', 'DESC');
                },
                'attr' => ['class' => 'chosen'],
                'required' => false,
                //'choices' => [],
            ])
            // "transformer le FR-PV du PSMF en variables locales"
            // ->add('frRPV', EntityType::class, [
            //     'label' => 'psmf.frRPV',
            //     'class' => 'App\Entity\UserManagement\User',
            //     'query_builder' => function (EntityRepository $er)  {
            //         return $er->createQueryBuilder('u')
            //             ->where('u.isEnable = true')
            //             ->andWhere('u.pvUser = true')
            //             ->orderBy('u.firstName', 'DESC');
            //     },
            //     'attr' => ['class' => 'chosen'],
            //     'required' => true,
            // ])            
            ->add('contactPvClient', EntityType::class, [
                'label' => 'psmf.contactPvClient',
                'label_attr' =>  ['id' => 'psmf_contactPvClient'],
                'class' => 'App\Entity\UserManagement\User',
                'query_builder' => function (EntityRepository $er) use ($contactPvClient) {
                    return $er->createQueryBuilder('u')
                        ->join('u.workRoles', 'wr')
                        ->where('u.isEnable = true AND u.pvUser = true AND wr.id = :workRole')
                        ->setParameters([
                            'workRole' => $contactPvClient->getId()
                        ])
                        ->orderBy('u.firstName', 'DESC');
                },
                'attr' => ['class' => 'chosen'],
                'required' => true,
                //'choices' => [],
                //'help' => 'SYS_CONTACT_PV_CLIENT_XX'
            ]) 
            ->add('activitesUM', EntityType::class, [
                'label' => 'psmf.activitesUM',
                'label_attr' =>  ['id' => 'psmf_activitesUM'],
                'class' => 'App\Entity\LovManagement\ActiviteUM',
                'query_builder' => static function (EntityRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->where('a.isValid = true')
                        ->orderBy('a.sort', 'ASC');
                },
                //'attr' => ['class' => 'js-select2'],
                'attr' => ['class' => 'chosen'],
                'multiple' => true,
                'required' => false,
                //'help' => '_if_UM_EU_QPPV_BACKUP_ ... _endif_'
            ]) 
            ->add('localQPPVPays', EntityType::class, [ // localQPPVs => localQPPVOther
                'label' => 'psmf.localQPPVOther',
                'label_attr' =>  ['id' => 'psmf_localQPPVOther'],
                'class' => 'App\Entity\LovManagement\Pays',
                'query_builder' => static function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->where('p.isValid = true')
                        ->orderBy('p.title', 'ASC');
                },
                //'attr' => ['class' => 'js-select2'],
                'attr' => ['class' => 'chosen'],
                'multiple' => true,
                'required' => false,
                //'help' => '_if_LOCAL_QPPV_AUSTRIA_OTHER_ ... _endif_'
            ])      
            ->add('localQPPVUM', EntityType::class, [
                'label' => 'psmf.localQPPVUM',
                'label_attr' =>  ['id' => 'psmf_localQPPVUM'],
                'class' => 'App\Entity\LovManagement\LocalQPPV',
                'query_builder' => static function (EntityRepository $er) {
                    return $er->createQueryBuilder('l')
                        ->where('l.isValid = true')
                        ->orderBy('l.title', 'ASC');
                },
                //'attr' => ['class' => 'js-select2'],
                'constraints' => [new LocalQPPVUMUnique()],
                'attr' => ['class' => 'chosen'],
                'multiple' => true,
                'required' => false,
                //'help' => '_if_LOCAL_QPPV_AUSTRIA_UM_ ... _endif_'
            ]) 
            ->add('basePV', EntityType::class, [
                'label' => 'psmf.basePV',
                'label_attr' =>  ['id' => 'psmf_basePV'],
                'class' => 'App\Entity\LovManagement\BasePV',
                'choice_label' => 'title',
                'query_builder' => function (EntityRepository $er)  {
                    return $er->createQueryBuilder('b')
                        ->where('b.isValid = true')
                        ->orderBy('b.sort', 'ASC');
                },
                'attr' => ['class' => 'chosen'],
                'required' => false,
                //'help' => '_if_PV_DATABASE_EVEREPORT_ et... _endif_'
            ])
            ->add('hasOtherPVProviders', CheckboxType::class, [
                    'label' => 'psmf.hasOtherPVProviders',
                    'label_attr' =>  ['id' => 'psmf_hasOtherPVProviders'],
                    'required' => false,
                    //'help' => '_if_UM_ONLY_PV_PROVIDER_ et _if_UM_NOT_ONLY_PV_PROVIDER_ ... _endif_'
                ]) 
            ->add('isOldClientBbac', CheckboxType::class, [
                    'label' => 'psmf.isOldClientBbac',
                    'label_attr' =>  ['id' => 'psmf_isOldClientBbac'],
                    'required' => false,
                    //'help' => '_if_OLD_CLINET_BBAC_ et _if_NOT_OLD_CLINET_BBAC_ ... _endif_'
                ])             
        ;

        $builder->addEventSubscriber(new AddEuqppvEntityFieldSubscriber($this->em))
            ->get('euqppvEntity')->addEventSubscriber(new UpdateEuqppvEntityFieldSubscriber($this->em));

        $builder->addEventSubscriber(new AddClientFieldSubscriber($this->em, $this->security))
            ->get('client')->addEventSubscriber(new UpdateClientFieldSubscriber($this->em));    

        //$builder->addEventSubscriber(new AddlocalQPPVPaysFieldSubscriber($this->em))
        //    ->get('localQPPVPays')->addEventSubscriber(new UpdatelocalQPPVPaysFieldSubscriber($this->em));               
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PSMF::class,
        ]);
    }
}
