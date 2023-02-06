<?php

namespace App\Validator\PSMFManagement;

use App\Entity\TemplateManagement\CorrespondanceLocaleHistory;
use App\Repository\TemplateManagement\VariableRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class LocalQPPVUMUniqueValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (null === $value || '' === $value) {
            return;
        }
        
        //dd($this->context->getRoot()->getData()); exit();
        if ($this->context->getRoot()->getData() instanceof CorrespondanceLocaleHistory)  {
            $psmf = $this->context->getRoot()->get('psmf')->getData();

            foreach ($psmf->getLocalQPPVPays() as $localQPPVPay) {
                foreach ($value as $localQPPVUM) {
                    if ($localQPPVUM->getTitle() == $localQPPVPay->getTitle()) {
                            $this->context->buildViolation($constraint->message)
                                ->addViolation();
                            return;
                    }
                }
            }   
        } else {
            $localQPPVPays = $this->context->getRoot()->get('localQPPVPays')->getData();

            foreach ($localQPPVPays as $localQPPVPay) {
                foreach ($value as $localQPPVUM) {
                    if ($localQPPVUM->getTitle() == $localQPPVPay->getTitle()) {
                            $this->context->buildViolation($constraint->message)
                                ->addViolation();
                            return;
                    }
                }
            }   
        }

        return;
    }
}