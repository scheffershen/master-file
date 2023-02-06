<?php

namespace App\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait TitleTrait
{
    /** @ORM\Column(name="description", type="text", nullable=true) */
    protected ?string $description = null;

    /**
     * @ORM\Column(length=250)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=250)
     */
    protected ?string $title = null;

    public function __toString()
    {
        return $this->title;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }
}
