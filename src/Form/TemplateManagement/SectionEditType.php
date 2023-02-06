<?php

namespace App\Form\TemplateManagement;

use App\Form\TemplateManagement\EventSubscriber\SectionPreSetDataSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormBuilderInterface;

class SectionEditType extends SectionType
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
        
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->addEventSubscriber(new SectionPreSetDataSubscriber($this->em)); 
    }

}
