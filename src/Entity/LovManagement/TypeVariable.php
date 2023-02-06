<?php

namespace App\Entity\LovManagement;

use App\Model\Lov as baseLov;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LovManagement\TypeVariableRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class TypeVariable extends baseLov
{
    public const TEXT = 'TXT'; 
    public const INTGER = 'INT';  
    public const IMAGE = 'IMG';  
    public const DATE = 'DATE';  
    public const TEXT_LONG = 'LTXT'; 
    public const OPTION = 'OPTION'; 

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    public function getId()
    {
        return $this->id;
    }
}
