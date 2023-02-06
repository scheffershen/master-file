<?php

namespace App\Entity\LovManagement;

use App\Model\Lov as baseLov;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LovManagement\LocalQPPVRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class LocalQPPV extends baseLov
{
    public const UM = 'um';
    public const OTHER = 'other';   

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
