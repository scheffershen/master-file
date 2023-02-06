<?php

namespace App\Message\TemplateManagement;

use App\Entity\TemplateManagement\Variable;

class VariableUpdated
{
    private $variable;
    private $old;
    private $action;

    public function __construct(string $action, array $old, Variable $variable)
    {
        $this->variable = $variable;
        $this->old = $old;    
        $this->action = $action;     
    }

    public function getVariable(): Variable
    {
        return $this->variable;
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
