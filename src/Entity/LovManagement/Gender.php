<?php

namespace App\Entity\LovManagement;

use App\Model\Lov as baseLov;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LovManagement\GenderRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Gender extends baseLov
{
    public const HOMME = 'GH'; 
    public const FEMME = 'GF';     
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
