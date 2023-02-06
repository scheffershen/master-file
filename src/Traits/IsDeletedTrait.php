<?php

namespace App\Traits;

/**
 * IsDeletedTrait
 *
 * @ORM\HasLifecycleCallbacks()
 * @author null
 */
trait IsDeletedTrait
{
    /**
     * @var bool
     *
     * @ORM\Column(name="is_deleted", type="boolean", nullable=false)
     */
    protected $isDeleted = false;

    /**
     * Set isDeleted
     *
     * @param bool $isDeleted
     * @return self
     */
    public function setIsDeleted(bool $isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * Get isDeleted
     *
     * @return bool
     */
    public function getIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    /**
     * isDeleted
     *
     * @return bool
     */
    public function isDeleted(): ?bool
    {
        return $this->isDeleted;
    }    
}
