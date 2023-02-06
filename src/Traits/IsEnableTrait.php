<?php

namespace App\Traits;

/**
 * IsEnableTrait
 *
 * @ORM\HasLifecycleCallbacks()
 */
trait IsEnableTrait
{
    /**
     * @var bool
     *
     * @ORM\Column(name="is_enable", type="boolean", nullable=false)
     */
    protected $isEnable = true;

    /**
     * Set isEnable
     *
     * @param bool $isEnable
     * @return self
     */
    public function setIsEnable(bool $isEnable)
    {
        $this->isEnable = $isEnable;

        return $this;
    }

    /**
     * Get isEnable
     *
     * @return bool
     */
    public function getIsEnable(): ?bool
    {
        return $this->isEnable;
    }

    /**
     * isEnable
     *
     * @return bool
     */
    public function isEnable(): ?bool
    {
        return $this->isEnable;
    }    
}
