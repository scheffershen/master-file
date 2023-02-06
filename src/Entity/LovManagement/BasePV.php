<?php

namespace App\Entity\LovManagement;

use App\Model\Lov as baseLov;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LovManagement\BasePVRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class BasePV extends baseLov
{
    public const EVEREPORT = 'EVEREPORT';
    public const SAFETY_EASY = 'SAFETY_EASY';   
    public const OTHER = 'OTHER';  

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
