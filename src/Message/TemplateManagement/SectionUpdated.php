<?php

namespace App\Message\TemplateManagement;

use App\Entity\TemplateManagement\Section;

class SectionUpdated
{
    private $section;
    private $old;
    private $action;

    public function __construct(string $action, array $old, Section $section)
    {
        $this->section = $section;
        $this->old = $old;   
        $this->action = $action;      
    }

    public function getSection(): Section
    {
        return $this->section;
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
