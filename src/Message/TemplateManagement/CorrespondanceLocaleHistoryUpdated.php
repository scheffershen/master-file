<?php

namespace App\Message\TemplateManagement;

use App\Entity\TemplateManagement\CorrespondanceLocaleHistory;

class CorrespondanceLocaleHistoryUpdated
{
    private $correspondanceLocaleHistory;
    private $old;
    private $action;

    public function __construct(string $action, array $old, CorrespondanceLocaleHistory $correspondanceLocaleHistory)
    {
        $this->correspondanceLocaleHistory = $correspondanceLocaleHistory;
        $this->old = $old;
        $this->action = $action;          
    }

    public function getCorrespondanceLocaleHistory(): CorrespondanceLocaleHistory
    {
        return $this->correspondanceLocaleHistory;
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
