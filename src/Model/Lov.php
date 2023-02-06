<?php

namespace App\Model;

use App\Traits\ActorTrait;
use App\Traits\DateTrait;
use App\Traits\IsValidTrait;
use App\Traits\RevisionTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Lov
 */
abstract class Lov
{
    use ActorTrait;
    use RevisionTrait;
    use DateTrait;
    use IsValidTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    protected $title;

    /**
     * @var int
     *
     * @ORM\Column(name="sort", type="integer", nullable=true)
     */
    protected $sort;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", nullable=false)
     */
    protected $code;

    public function __construct()
    {
        $this->sort = 0;
        $this->isValid = 1;
        $this->revision = -1;
    }

    public function __toString(): string
    {
        return $this->title;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return AnswerTypes
     */
    public function setTitle($title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set sort
     *
     * @param int $sort
     * @return LovType
     */
    public function setSort($sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * Get sort
     *
     * @return int
     */
    public function getSort(): ?int
    {
        return $this->sort;
    }

    /**
     * Get code
     * @return string
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * Set code
     *
     * @param string $code
     */
    public function setCode(?string $code): void
    {
        $this->code = $code;
    }
}
