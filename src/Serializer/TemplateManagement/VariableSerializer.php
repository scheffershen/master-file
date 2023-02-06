<?php

namespace App\Serializer\TemplateManagement;

use App\Entity\TemplateManagement\Variable;

class VariableSerializer
{
    public function serialize(Variable $variable)
    {
        return [
        		'label' => $variable->getLabel(),
                'balise' => $variable->getBalise(),
                'type' => (string)$variable->getType(),   
                'obligation' => (string)$variable->getObligation(),    
                'scope' => (string)$variable->getScope(),    
                //'classe' => (string)$variable->getClasses(),  
                'classes' => ($variable->getClasses())? json_encode($this->classesSerialize($variable->getClasses())):null,  
                'userHelp' => $variable->getUserHelp(), 
                'isValid' => $variable->getIsValid(),  
                'isDeleted' => $variable->getIsDeleted(),                   
            ];       	
    }

    private function classesSerialize($classes)
    {
        $result = [];
        foreach ($classes as $classe) {
            $result[] = $classe->getTitle();
        }

        return $result;
    }
}