<?php

namespace App\Message\TemplateManagement;

use App\Entity\TemplateManagement\CorrespondanceGlobaleHistory;

class CorrespondanceGlobaleHistoryUpdated
{
    private $correspondanceGlobaleHistory;
    private $old;
    private $action;

    public function __construct(string $action, array $old, CorrespondanceGlobaleHistory $correspondanceGlobaleHistory)
    {
        $this->correspondanceGlobaleHistory = $correspondanceGlobaleHistory;
        $this->old = $old;        
        $this->action = $action;   
    }

    public function getCorrespondanceGlobaleHistory(): CorrespondanceGlobaleHistory
    {
        return $this->correspondanceGlobaleHistory;
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
