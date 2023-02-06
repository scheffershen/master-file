<?php

namespace App\Serializer\TemplateManagement;

use App\Entity\TemplateManagement\CorrespondanceGlobaleHistory;

/**
 * CorrespondanceGlobaleHistorySerializer
 */
class CorrespondanceGlobaleHistorySerializer
{
    public function serialize(CorrespondanceGlobaleHistory $correspondanceGlobaleHistory)
    {
    	$correspondanceGlobale = [];
    	foreach ($correspondanceGlobaleHistory->getCorrespondances() as $key => $correspondance) {
            if ($correspondance->getVariable()->isValid()) {
                array_push($correspondanceGlobale, [(string)$correspondance->getVariable()->getBalise() => (string)$correspondance->getValueLocal()]); 
            }
    	}
    	return $correspondanceGlobale;    	
    }
}