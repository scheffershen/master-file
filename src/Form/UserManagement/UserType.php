<?php

namespace App\Form\UserManagement;

use App\Entity\UserManagement\User;
use App\Entity\UserManagement\Client;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;

class UserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', null, [
                'label' => 'label.username',
            ])
            ->add('email', null, [
                'label' => 'label.email',
            ])          
            ->add('firstName', null, [
                'attr' => [
                    'autofocus' => true,
                ],
                'required' => true,
                'label' => 'label.firstname',
            ])
            ->add('lastName', null, [
                'attr' => [
                    'autofocus' => true,
                ],
                'required' => true,
                'label' => 'label.lastname',
            ])
            ->add('roles', ChoiceType::class, [
                'multiple' => true,
                'expanded' => true,
                'required' =>true,
                'choices'  => USER::ROLES,
            ])                    
            ->add('isEnable', CheckboxType::class, [
                    'label' => 'user.isEnable',
                    'required' => false,
            ])
            ->add('clients', EntityType::class, [
                'label' => 'user.client',
                'class' => 'App\Entity\UserManagement\Client',
                'query_builder' => static function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.isValid = true')
                        ->orderBy('c.name', 'ASC');
                },
                'attr' => ['class' => 'chosen'],
                'multiple' => true,
                'required' => true,
            ]) 
            ->add('mailAlerte', CheckboxType::class, [
                    'label' => 'user.mailAlerte',
                    'required' => false,
            ])              
            ->add('pvUser', CheckboxType::class, [
                    'label' => 'user.pvUser',
                    'required' => false,
            ])     
            ->add('cv', VichFileType::class, [
                'required' => false,
                'allow_delete' => true,
                'download_uri' => false,
                'download_label' => false,
                'attr' => ['class' => 'pvUser'],
                'help' => 'Image in format .jpg, .png and .gif'
            ])   
            ->add('gender', null, [
                'label' => 'lov.gender',
                'attr' => ['class' => 'pvUser'],
            ])     
            // ->add('workRole', null, [
            //     'label' => 'lov.workRole',
            //     'attr' => ['class' => 'pvUser'],
            // ])  
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
                'required' => false,
                'help' => 'EUQPPV, Deputy EUQPPV, Contact PV client'
            ])                 
            ->add('workFunction', null, [
                'label' => 'user.workFunction',
                'attr' => ['class' => 'pvUser'],
            ])         
            ->add('mobile', null, [
                'label' => 'label.mobile',
                //'attr' => ['class' => 'pvUser'],
            ])
            ->add('fixe', null, [
                'label' => 'label.fixe',
                'attr' => ['class' => 'pvUser'],
            ])
            ->add('fax', null, [
                'label' => 'label.fax',
                'attr' => ['class' => 'pvUser'],
            ])            
            ->add('workAttachment', null, [
                'label' => 'lov.rattachement',
                'attr' => ['class' => 'pvUser'],
                //'help' => 'Entity EUQPPV'
            ])
            ->add('workName', null, [
                'label' => 'user.workName',
                'attr' => ['class' => 'pvUser'],
            ])
            ->add('adresse', null, [
                'label' => 'label.adresse',
                'attr' => ['class' => 'pvUser'],
            ])
            // ->add('reason', null, [
            //     'label' => 'section.reason',
            //     'attr' => [
            //         'class' => 'form-control',
            //         'rows' => '5',
            //     ],                
            //     'data' => '',
            //     'required' => true,
            //     'trim' => true,
            // ]) 
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }
}
