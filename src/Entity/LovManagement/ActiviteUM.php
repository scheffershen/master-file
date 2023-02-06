<?php

namespace App\Entity\LovManagement;

use App\Model\Lov as baseLov;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LovManagement\ActiviteUMRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ActiviteUM extends baseLov
{   
    public const EU_QPPV_back_up = 'EU_QPPV_BACKUP';
    public const CALL_MANAGEMENT_WORKING_HOURS = 'CALL_MANAGEMENT_WORKING_HOURS';
    public const CALL_MANAGEMENT_NON_WORKING_HOURS = 'CALL_MANAGEMENT_NON_WORKING_HOURS';
    public const CASE_MANAGEMENT = 'CASE_MANAGEMENT';
    public const ICSR_SUBMISSION = 'ICSR_SUBMISSION';
    public const LITERATURE_MONITORING = 'LITERATURE_MONITORING';
    public const SIGNAL_MANAGEMENT = 'SIGNAL_MANAGEMENT';
    public const PSUR_MANAGEMENT = 'PSUR_MANAGEMENT';
    public const RMP_MANAGEMENT = 'RMP_MANAGEMENT';
    public const VARIATION_MANAGEMENT = 'VARIATION_MANAGEMENT';
    public const RESPONSES_TO_THE_AUTHORITIES = 'RESPONSES_TO_THE_AUTHORITIES';           
    public const COMMUNICATION_OF_SAFETY_CONCERNS = 'COMMUNICATION_OF_SAFETY_CONCERNS';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    public function getId()
    {
        return $this->id;
    }
}
