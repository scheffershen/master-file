<?php

namespace App\Message\PSMFManagement;

use App\Entity\PSMFManagement\PSMF;

class PSMFUpdated
{
    private $pSMF;
    private $old;
    private $action;

    public function __construct(string $action, array $old, PSMF $pSMF)
    {
        $this->pSMF = $pSMF;
        $this->old = $old;
        $this->action = $action;          
    }

    public function getPSMF(): PSMF
    {
        return $this->pSMF;
    }

    public function getOld(): array 
    {
    	return $this->old;
    }

    public function getAction(): string 
    {
        return $this->action;
    }     
}
