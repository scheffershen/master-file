<?php

namespace App\Entity\LovManagement;

use App\Model\Lov as baseLov;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LovManagement\ScopeRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Scope extends baseLov
{
    public const GLOBALE = 'SG'; 
    public const LOCALE = 'SL'; 
    public const SYETEME = 'SS';             
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
