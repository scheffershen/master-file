<?php

namespace App\Validator\PSMFManagement;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class LocalQPPVUMUnique extends Constraint
{
    /**
     * @var string
     */	
    public $message = "Des pays confondent avec Local QPPV Other";
}