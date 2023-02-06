<?php

namespace App\Entity\LovManagement;

use App\Model\Lov as baseLov;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LovManagement\ObligationRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Obligation extends baseLov
{
    public const OBLIGATOIRE = 'VO';
    public const FACULTATIVE = 'VF';   

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
