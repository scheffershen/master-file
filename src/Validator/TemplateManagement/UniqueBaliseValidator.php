<?php

namespace App\Validator\TemplateManagement;

use App\Repository\TemplateManagement\VariableRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueBaliseValidator extends ConstraintValidator
{
    private $variableRepository;

    public function __construct(VariableRepository $variableRepository)
    {
        $this->variableRepository = $variableRepository;
    }

    public function validate($value, Constraint $constraint)
    {

        if (null === $value || '' === $value) {
            return;
        }

        $_variable = $this->context->getRoot()->getData();

        $variables = $this->variableRepository->findBy(['isDeleted' => false]);

        foreach ($variables as $variable) {
            if ($_variable->getId() != $variable->getId()) {
                if (strpos($variable->getBalise(), $value) !== false) {
                    $this->context->buildViolation($constraint->message)
                        ->setParameter('{{ string }}', $variable->getBalise())
                        ->addViolation();
                    return;
                }
            }
        }
        return;
    }
}