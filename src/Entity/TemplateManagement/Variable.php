<?php

namespace App\Entity\TemplateManagement;

use App\Entity\LovManagement\Obligation;
use App\Entity\LovManagement\Scope;
use App\Entity\LovManagement\TypeVariable;
use App\Entity\PSMFManagement\PSMFHistory;
use App\Entity\TemplateManagement\Classe;
use App\Traits\ActorTrait;
use App\Traits\DateTrait;
use App\Traits\IsValidTrait;
use App\Traits\IsDeletedTrait;
use App\Traits\ReasonTrait;
use App\Repository\TemplateManagement\VariableRepository;
use App\Validator\TemplateManagement\FormatBalise; 
use DH\DoctrineAuditBundle\Annotation as Audit;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=VariableRepository::class)
 * @UniqueEntity(fields="label", message="Label is already taken.")
 * @UniqueEntity(fields="balise", message="Balise is already taken.") 
 * @ORM\HasLifecycleCallbacks()  
 * @Audit\Auditable
 * @Audit\Security(view={"ROLE_AUDIT"}) 
 */
class Variable
{
    use ActorTrait;
    use DateTrait;
    use IsValidTrait;    
    use IsDeletedTrait;
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
    private $label;

    /**
     * @ORM\Column(type="string", length=255)
     * @FormatBalise
     */
    private $balise;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=TypeVariable::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity=Obligation::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $obligation;

    /**
     * @ORM\ManyToOne(targetEntity=Scope::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $scope;

    /**
     * @ORM\ManyToMany(targetEntity=Classe::class, inversedBy="variables")
     */    
    private $classes;

    /**
     * @ORM\Column(type="text", nullable=false)
     */
    private $userHelp;

    /**
     * @ORM\OneToMany(targetEntity=Correspondance::class, mappedBy="variable", orphanRemoval=true)
     */
    protected $correspondances;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isAnnexe;

    /**
     * @ORM\OneToMany(targetEntity=PSMFHistory::class, mappedBy="variable")
     */
    private $pSMFHistories;

    public function __construct()
    {
        $this->isValid = true;
        $this->isDeleted = false;
        $this->isAnnexe = true;
        $this->classes = new ArrayCollection(); 
        $this->correspondances = new ArrayCollection();
        $this->pSMFHistories = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->balise;
    } 
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getBalise(): ?string
    {
        return trim($this->balise);
    }

    public function setBalise(string $balise): self
    {
        $this->balise = trim($balise);

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getType(): ?TypeVariable
    {
        return $this->type;
    }

    public function setType(?TypeVariable $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getObligation(): ?Obligation
    {
        return $this->obligation;
    }

    public function setObligation(?Obligation $obligation): self
    {
        $this->obligation = $obligation;

        return $this;
    }

    public function getScope(): ?Scope
    {
        return $this->scope;
    }

    public function setScope(?Scope $scope): self
    {
        $this->scope = $scope;

        return $this;
    }

    public function getClasses(): Collection
    {
        return $this->classes;
    }

    public function addClasse(Classe $classe): self
    {
        if (!$this->classes->contains($classe)) {
            $this->classes[] = $classe;
            $classe->addVariable($this);
        }

        return $this;
    }

    public function removeClasse(Classe $classe): self
    {
        if ($this->classes->removeElement($classe)) {
            $classe->removeVariable($this);
        }

        return $this;
    }

    public function getUserHelp(): ?string
    {
        return $this->userHelp;
    }

    public function setUserHelp(?string $userHelp): self
    {
        $this->userHelp = $userHelp;

        return $this;
    }

    /**
     * @return Collection|Correspondance[]
     */
    public function getCorrespondances(): Collection
    {
        return $this->correspondances;
    }

    public function addCorrespondance(Correspondance $correspondance): self
    {
        if (!$this->correspondances->contains($correspondance)) {
            $this->correspondances[] = $correspondance;
            $correspondance->setVariable($this);
        }

        return $this;
    }

    public function removeCorrespondance(Correspondance $correspondance): self
    {
        if ($this->correspondances->contains($correspondance)) {
            $this->correspondances->removeElement($correspondance);
            // set the owning side to null (unless already changed)
            if ($correspondance->getVariable() === $this) {
                $correspondance->setVariable(null);
            }
        }

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
    * functions for contollers and templates
    */

    public function hasCorrespondanceGlobale(): ?Correspondance
    {
        if ($this->getScope()->getCode() == Scope::GLOBALE && $this->correspondances->count() > 0) {
            return $this->correspondances->first();
        }
        return null;
    }

    public function getCorrespondanceGlobale(): ?Correspondance
    {
        return $this->hasCorrespondanceGlobale();
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
            $pSMFHistory->setVariable($this);
        }

        return $this;
    }

    public function removePSMFHistory(PSMFHistory $pSMFHistory): self
    {
        if ($this->pSMFHistories->removeElement($pSMFHistory)) {
            // set the owning side to null (unless already changed)
            if ($pSMFHistory->getVariable() === $this) {
                $pSMFHistory->setVariable(null);
            }
        }

        return $this;
    }    

    private $pSMFs = [];
    public function getPSMFs(): ?array
    {
        foreach ($this->getCorrespondances() as $correspondance) {
            if (!in_array($correspondance->getPsmf(), $this->pSMFs)) {
                $this->pSMFs[] = $correspondance->getPsmf();
            }
        }
        return $this->pSMFs;
    }    
}
