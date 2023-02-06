<?php

namespace App\Traits;

/**
 * IsValidTrait
 *
 * @ORM\HasLifecycleCallbacks()
 */
trait IsValidTrait
{
    /**
     * @var bool
     *
     * @ORM\Column(name="is_valid", type="boolean", nullable=false)
     */
    protected $isValid = true;

    /**
     * Set isValid
     *
     * @param bool $isValid
     * @return self
     */
    public function setIsValid(bool $isValid)
    {
        $this->isValid = $isValid;

        return $this;
    }

    /**
     * Get isValid
     *
     * @return bool
     */
    public function getIsValid(): ?bool
    {
        return $this->isValid;
    }

    /**
     * isValid
     *
     * @return bool
     */
    public function isValid(): ?bool
    {
        return $this->isValid;
    }    
}
