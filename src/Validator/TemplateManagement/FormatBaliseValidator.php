<?php

namespace App\Validator\TemplateManagement;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class FormatBaliseValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (null === $value || '' === $value) {
            return;
        }

        if ($value) {
            // de majuscules, de chiffres et de traits de soulignement
            if (!preg_match('/^[A-Z0-9_]+$/', $value, $matches) ) { 
                $this->context->buildViolation($constraint->message)
                    ->addViolation();
            }
        }
    }
}