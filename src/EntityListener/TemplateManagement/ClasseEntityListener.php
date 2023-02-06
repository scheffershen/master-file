<?php

namespace App\EntityListener\TemplateManagement;

use App\Entity\TemplateManagement\Classe;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\String\Slugger\SluggerInterface;

class ClasseEntityListener
{
    private $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function prePersist(Classe $classe, LifecycleEventArgs $event)
    {
        $classe->computeCode($this->slugger);
    }

    public function preUpdate(Classe $classe, LifecycleEventArgs $event)
    {
        $classe->computeCode($this->slugger);
    }
}