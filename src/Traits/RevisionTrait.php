<?php

namespace App\Traits;

/**
 * RevisionTrait
 *
 * @ORM\HasLifecycleCallbacks()
 */
trait RevisionTrait
{
    /**
     * @var int
     *
     * @ORM\Column(name="revision", type="integer", nullable=false)
     */
    protected $revision;

    /**
     * Set revision
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setRevision()
    {
        if (isset($this->revision)) {
            $this->revision = $this->revision + 1;
        } else {
            $this->revision = 0;
        }

        return $this;
    }

    /**
     * Get revision
     *
     * @return int
     */
    public function getRevision(): ?int
    {
        return $this->revision;
    }
}
