<?php

namespace App\Entity\TemplateManagement;

use App\Repository\TemplateManagement\TemplateRepository;
use App\Traits\ReasonTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TemplateRepository::class)
 */
class Template
{
    public const TEMPLATE_ID = 1; 
    public const HELP_ID = 2;

    use ReasonTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Section::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $header;

    /**
     * @ORM\OneToMany(targetEntity=Section::class, mappedBy="template")
     */
    private $sections;

    /**
     * @ORM\ManyToOne(targetEntity=Section::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $footer;

    public function __construct()
    {
        $this->sections = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHeader(): ?Section
    {
        return $this->header;
    }

    public function setHeader(?Section $header): self
    {
        $this->header = $header;

        return $this;
    }

    /**
     * @return Collection|Section[]
     */
    public function getSections(): Collection
    {
        return $this->sections;
    }

    public function addSection(Section $section): self
    {
        if (!$this->sections->contains($section)) {
            $this->sections[] = $section;
            $section->setTemplate($this);
        }

        return $this;
    }

    public function removeSection(Section $section): self
    {
        if ($this->sections->contains($section)) {
            $this->sections->removeElement($section);
            // set the owning side to null (unless already changed)
            if ($section->getTemplate() === $this) {
                $section->setTemplate(null);
            }
        }

        return $this;
    }

    public function getFooter(): ?Section
    {
        return $this->footer;
    }

    public function setFooter(?Section $footer): self
    {
        $this->footer = $footer;

        return $this;
    }

    /**
     * ======================================
     *  function for contoller and templates
     * =====================================
     */
    public function getLastPosition()
    {
        $lastPosition = 1;
        if (count($this->getSections()) > 0) {
            foreach ($this->getSections() as $section) {
                if ($section->getIsDeleted() === false) {
                    if ($section->getPosition() > $lastPosition) {
                        $lastPosition = $section->getPosition() + 1;
                    }
                }
            }
        } 
        return $lastPosition;
    }       
}
