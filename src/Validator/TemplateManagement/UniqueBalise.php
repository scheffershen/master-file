<?php

namespace App\Validator\TemplateManagement;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueBalise extends Constraint
{
    /**
     * @var string
     */	
    public $message = "Cette balise confuse avec celle-ci: {{ string }}";
}