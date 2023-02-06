<?php

namespace App\Traits;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * UuidTrait.
 */
trait UuidTrait
{
    /** @ORM\Column("uuid", type="string", length=36, unique=true) */
    private string $uuid;

    /**
     * Gets UUID.
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * Sets UUID.
     *
     * @param $uuid
     */
    public function setUuid($uuid): void
    {
        $this->uuid = $uuid;
    }

    public function refreshUuid(): void
    {
        $this->uuid = Uuid::uuid4()->toString();
    }
}
