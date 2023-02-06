<?php

namespace App\Traits;

use Doctrine\ORM\Mapping as ORM;

trait ReasonTrait
{
    /** @ORM\Column(name="reason", type="text", nullable=true) */
    protected ?string $reason = null;

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): void
    {
        $this->reason = $reason;
    }
}