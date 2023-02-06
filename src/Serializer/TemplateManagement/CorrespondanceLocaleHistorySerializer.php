<?php

namespace App\Serializer\TemplateManagement;

use App\Entity\PSMFManagement\PSMF;
use App\Entity\TemplateManagement\CorrespondanceLocaleHistory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * CorrespondanceLocaleHistorySerializer
 */
class CorrespondanceLocaleHistorySerializer
{
    public function serialize(CorrespondanceLocaleHistory $correspondanceLocaleHistory)
    {
    	$correspondanceLocale = [];
    	
    	array_push($correspondanceLocale, ['title' => (string)$correspondanceLocaleHistory->getPsmf()->getTitle()]); 
    	array_push($correspondanceLocale, ['client' => (string)$correspondanceLocaleHistory->getPsmf()->getClient()]); 
    	array_push($correspondanceLocale, ['euqppvEntity' => (string)$correspondanceLocaleHistory->getPsmf()->getEuqppvEntity()]); 
    	array_push($correspondanceLocale, ['eudravigNum' => (string)$correspondanceLocaleHistory->getPsmf()->getEudravigNum()]); 
    	array_push($correspondanceLocale, ['euQPPV' => (string)$correspondanceLocaleHistory->getPsmf()->getEuQPPV()]); 
    	if ($correspondanceLocaleHistory->getPsmf()->getDeputyEUQPPV()) {
    		array_push($correspondanceLocale, ['deputyEUQPPV' => (string)$correspondanceLocaleHistory->getPsmf()->getDeputyEUQPPV()]); 
    	}
    	array_push($correspondanceLocale, ['contactPvClient' => (string)$correspondanceLocaleHistory->getPsmf()->getContactPvClient()]);

        if (!$correspondanceLocaleHistory->getPsmf()->getActivitesUM()->isEmpty()) { // ManyToMany activitesUM
            array_push($correspondanceLocale, ['activitesUM' => (string)$this->getValues($correspondanceLocaleHistory->getPsmf()->getActivitesUM())]);
        }

        if (!$correspondanceLocaleHistory->getPsmf()->getLocalQPPVPays()->isEmpty()) { // ManyToMany localQPPVPays
            array_push($correspondanceLocale, ['localQPPVPays' => (string)$this->getValues($correspondanceLocaleHistory->getPsmf()->getLocalQPPVPays())]);
        }        

        if (!$correspondanceLocaleHistory->getPsmf()->getLocalQPPVUM()->isEmpty()) { // ManyToMany localQPPVUM
            array_push($correspondanceLocale, ['localQPPVUM' => (string)$this->getValues($correspondanceLocaleHistory->getPsmf()->getLocalQPPVUM())]);
        }        
        
        if ($correspondanceLocaleHistory->getPsmf()->getBasePV()) {
            array_push($correspondanceLocale, ['basePV' => (string)$correspondanceLocaleHistory->getPsmf()->getBasePV()]);
        }
        if ($correspondanceLocaleHistory->getPsmf()->getHasOtherPVProviders()) {
            array_push($correspondanceLocale, ['hasOtherPVProviders' => $correspondanceLocaleHistory->getPsmf()->getHasOtherPVProviders()]);
        }
        if ($correspondanceLocaleHistory->getPsmf()->getIsOldClientBbac()) {
            array_push($correspondanceLocale, ['isOldClientBbac' => $correspondanceLocaleHistory->getPsmf()->getIsOldClientBbac()]);
    	}

        foreach ($correspondanceLocaleHistory->getCorrespondances() as $key => $correspondance) {
            if ($correspondance->getVariable()->isValid()) {
    		    array_push($correspondanceLocale, [(string)$correspondance->getVariable()->getBalise() => (string)$correspondance->getValueLocal()]); 
            }
    	}
    	return $correspondanceLocale;
    }

    private function getValues(Collection $lovs) {
        $results = '';
        foreach ($lovs as $lov) {
            $results .= $lov->getTitle().', ';
        }
        return $results;
    }
}