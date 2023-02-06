<?php

namespace App\Validator\TemplateManagement;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class FormatBalise extends Constraint
{
    /**
     * @var string
     */	
    public $message = "La balise ne doit être composée que de majuscules, de chiffres et de traits de soulignement";
}