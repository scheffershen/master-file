<?php

namespace App\Entity\TemplateManagement;

use App\Entity\PSMFManagement\PSMFHistory;
use App\Traits\ActorTrait;
use App\Traits\DateTrait;
use App\Traits\IsValidTrait;
use App\Traits\IsDeletedTrait;
use App\Traits\IsMajeurTrait;
use App\Traits\ReasonTrait;
use App\Repository\TemplateManagement\SectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DH\DoctrineAuditBundle\Annotation as Audit;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=SectionRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(fields="title", message="title is already taken.")
 * @Audit\Auditable
 * @Audit\Security(view={"ROLE_AUDIT"})    
 */
class Section
{
    public const HEADER_ID = 1;
    public const FOOTER_ID = 2;
    public const PV_SYSTEM_MASTER_ID = 3;
    public const TABLE_CONTENTS_ID = 4;
    public const ABBREVIATIONS_ID = 5;

    use ActorTrait;
    use DateTrait;
    use IsValidTrait;    
    use IsDeletedTrait; 
    use IsMajeurTrait;
    use ReasonTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $position;

    /**
     * @ORM\Column(type="text")
     */
    private $contenu;

    /**
     * @ORM\Column(type="boolean")
     */
    private $contenuEditable;

    /**
     * @ORM\ManyToOne(targetEntity=Section::class, inversedBy="sections")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity=Section::class, mappedBy="parent")
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $sections;

    /**
     * @ORM\ManyToOne(targetEntity=Template::class, inversedBy="sections")
     */
    private $template;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isAnnexe;

    /**
     * @ORM\OneToMany(targetEntity=PSMFHistory::class, mappedBy="section")
     */
    private $pSMFHistories;

    /**
     * @ORM\Column(type="boolean")
     */
    private $allowSubSection;

    /**
     * @ORM\Column(type="boolean")
     */
    private $editable;

    /**
     * @ORM\ManyToMany(targetEntity=Classe::class, mappedBy="sections")
     */
    protected $classes;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPageBreak;

    public function __construct()
    {
        $this->sections = new ArrayCollection();
        $this->pSMFHistories = new ArrayCollection();
        $this->editable = true;
        $this->contenuEditable = true;
        $this->classes = new ArrayCollection(); 
    }

    public function __toString()
    {
        return $this->title;
    }   
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): self
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getContenuEditable(): ?bool
    {
        return $this->contenuEditable;
    }

    public function setContenuEditable(bool $contenuEditable): self
    {
        $this->contenuEditable = $contenuEditable;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getSections(): Collection
    {
        return $this->sections;
    }

    public function addSection(self $section): self
    {
        if (!$this->sections->contains($section)) {
            $this->sections[] = $section;
            $section->setParent($this);
        }

        return $this;
    }

    public function removeSection(self $section): self
    {
        if ($this->sections->contains($section)) {
            $this->sections->removeElement($section);
            // set the owning side to null (unless already changed)
            if ($section->getParent() === $this) {
                $section->setParent(null);
            }
        }

        return $this;
    }

    public function getTemplate(): ?Template
    {
        return $this->template;
    }

    public function setTemplate(?Template $template): self
    {
        $this->template = $template;

        return $this;
    }

    public function getIsAnnexe(): ?bool
    {
        return $this->isAnnexe;
    }

    public function setIsAnnexe(bool $isAnnexe): self
    {
        $this->isAnnexe = $isAnnexe;

        return $this;
    }   

    /**
     * @return Collection|PSMFHistory[]
     */
    public function getPSMFHistories(): Collection
    {
        return $this->pSMFHistories;
    }

    public function addPSMFHistory(PSMFHistory $pSMFHistory): self
    {
        if (!$this->pSMFHistories->contains($pSMFHistory)) {
            $this->pSMFHistories[] = $pSMFHistory;
            $pSMFHistory->setSection($this);
        }

        return $this;
    }

    public function removePSMFHistory(PSMFHistory $pSMFHistory): self
    {
        if ($this->pSMFHistories->removeElement($pSMFHistory)) {
            // set the owning side to null (unless already changed)
            if ($pSMFHistory->getSection() === $this) {
                $pSMFHistory->setSection(null);
            }
        }

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

    public function getAllowSubSection(): ?bool
    {
        return $this->allowSubSection;
    }

    public function setAllowSubSection(bool $allowSubSection): self
    {
        $this->allowSubSection = $allowSubSection;

        return $this;
    }

    public function getEditable(): ?bool
    {
        return $this->editable;
    }

    public function setEditable(bool $editable): self
    {
        $this->editable = $editable;

        return $this;
    }

    /**
     * @return Collection|Classe[]
     */
    public function getClasses(): Collection
    {
        return $this->classes;
    }

    public function addClasse(Classe $classe): self
    {
        if (!$this->classes->contains($classe)) {
            $this->classes[] = $classe;
            $classe->addSection($this);
        }

        return $this;
    }

    public function removeClasse(Classe $classe): self
    {
        if ($this->classes->removeElement($classe)) {
            $classe->removeSection($this);
        }

        return $this;
    }

    public function getIsPageBreak(): ?bool
    {
        return $this->isPageBreak;
    }

    public function setIsPageBreak(bool $isPageBreak): self
    {
        $this->isPageBreak = $isPageBreak;

        return $this;
    }

}
