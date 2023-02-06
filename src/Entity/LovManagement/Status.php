<?php

namespace App\Entity\LovManagement;

use App\Model\Lov as baseLov;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LovManagement\StatusRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Status extends baseLov
{
    public const PUBLISHED = 'PUB'; 
    public const DOWNLOADED = 'DLD';
    public const APPLICABLE = 'APL'; 
    public const ARCHIVE = 'ARC';

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
