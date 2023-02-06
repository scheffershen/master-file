<?php

namespace App\Traits;

/**
 * IsMajeurTrait
 *
 * @ORM\HasLifecycleCallbacks()
 */
trait IsMajeurTrait
{
    /**
     * @var bool
     *
     * @ORM\Column(name="is_majeur", type="boolean", nullable=false)
     */
    protected $isMajeur = true;

    /**
     * Set isMajeur
     *
     * @param bool $isMajeur
     * @return self
     */
    public function setIsMajeur(bool $isMajeur)
    {
        $this->isMajeur = $isMajeur;

        return $this;
    }

    /**
     * Get isMajeur
     *
     * @return bool
     */
    public function getIsMajeur(): ?bool
    {
        return $this->isMajeur;
    }
  
    /**
     * isMajeur
     *
     * @return bool
     */
    public function isMajeur(): ?bool
    {
        return $this->isMajeur;
    }  
}
