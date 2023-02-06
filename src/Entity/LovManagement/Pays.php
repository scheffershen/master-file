<?php

namespace App\Entity\LovManagement;

use App\Model\Lov as baseLov;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LovManagement\PaysRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Pays extends baseLov
{ 
    public const AUSTRIA = 'AUSTRIA';
    public const BELGIUM = 'BELGIUM';
    public const BULGARIA = 'BULGARIA';
    public const CROATIA = 'CROATIA';
    public const CYPRUS = 'CYPRUS';
    public const CZECH_REPUBLIC = 'CZECH_REPUBLIC';
    public const DENMARK = 'DENMARK';
    public const ESTONIA = 'ESTONIA';
    public const FINLAND = 'FINLAND';
    public const FRANCE = 'FRANCE';
    public const ICELAND = 'ICELAND';           
    public const GERMANY = 'GERMANY';
    public const GREECE = 'GREECE'; 
    public const HUNGARY = 'HUNGARY';
    public const SOUTHERN = 'SOUTHERN'; 
    public const ITALY = 'ITALY';
    public const LATVIA = 'LATVIA';                                                             
    public const POLAND = 'POLAND';
    public const PORTUGAL = 'PORTUGAL';
    public const ROMANIA = 'ROMANIA';
    public const IRELAND = 'IRELAND';  
    public const SLOVAKIA = 'SLOVAKIA';   
    public const SLOVENIA = 'SLOVENIA';
    public const SPAIN = 'SPAIN';
    public const LITHUANIA = 'LITHUANIA';
    public const SWEDEM = 'SWEDEM';
    public const LUXEMBOURG = 'LUXEMBOURG';    
    public const UNITED_KINGDOM = 'UNITED KINGDOM';
    public const NETHERLAND = 'NETHERLAND';         
    public const MALTA = 'MALTA';  
    public const NORWAY = 'NORWAY'; 
    
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
