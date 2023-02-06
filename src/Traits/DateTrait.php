<?php

namespace App\Traits;

/**
 * dateTrait
 *
 * @ORM\HasLifecycleCallbacks()
 */
trait DateTrait
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_date", type="datetime", nullable=false)
     */
    protected $createDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_date", type="datetime", nullable=false)
     */
    protected $updateDate;

    /**
     * Set createDate
     *
     * @ORM\PrePersist
     */
    public function setCreateDate()
    {
        $this->createDate = new \DateTime();

        return $this;
    }

    /**
     * Get createDate
     *
     * @return \DateTime
     */
    public function getCreateDate(): ? \Datetime
    {
        return $this->createDate;
    }

    /**
     * Set updateDate
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setUpdateDate()
    {
        $this->updateDate = new \DateTime();

        return $this;
    }

    /**
     * Get updateDate
     *
     * @return \DateTime
     */
    public function getUpdateDate(): ? \Datetime
    {
        return $this->updateDate;
    }
}
