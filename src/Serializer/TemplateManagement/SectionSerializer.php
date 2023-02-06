<?php

namespace App\Serializer\TemplateManagement;

use App\Entity\TemplateManagement\Section;

/**
 * SectionSerializer
 */
class SectionSerializer
{
    public function serialize(Section $section)
    {
        return [
        		'title' => $section->getTitle(),
                'contenu' => $section->getContenu(),
                'position' => $section->getPosition(),   
                'parent' => (string)$section->getParent(),
                'isValid' => $section->getIsValid(),  
                'isDeleted' => $section->getIsDeleted(),       
            ];    	
    }
}