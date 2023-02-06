<?php

namespace App\Serializer\PSMFManagement;

use App\Entity\PSMFManagement\PSMF;

/**
 * PSMFSerializer
 */
class PSMFSerializer
{
    public function serialize(PSMF $pSMF)
    {
        return [
        		'title' => $pSMF->getTitle(),
                'client' => (string)$pSMF->getClient(),
                'euqppvEntity' => (string)$pSMF->getEuqppvEntity(),
        		'eudravigNum' => (string)$pSMF->getEudravigNum(),
                'euQPPV' => (string)$pSMF->getEuQPPV(),
                'deputyEUQPPV' => (string)$pSMF->getDeputyEUQPPV(),  
                'contactPvClient' => (string)$pSMF->getContactPvClient(),                             
            ];      	
    }
}