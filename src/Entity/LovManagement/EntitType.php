<?php

namespace App\Entity\LovManagement;

use App\Model\Lov as baseLov;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LovManagement\EntitTypeRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class EntitType extends baseLov
{
    public const UM = 'um';
    public const CLIENT = 'client';   
    public const PRESTA = 'presta';  

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
