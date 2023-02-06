<?php

namespace App\Entity\TemplateManagement;

use App\Model\Lov as baseLov;
use App\Traits\IsDeletedTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DH\DoctrineAuditBundle\Annotation as Audit;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TemplateManagement\ClasseRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(fields="title", message="title is already taken.")
 * @UniqueEntity(fields="code", message="Code is already taken.") 
 * @Audit\Auditable
 * @Audit\Security(view={"ROLE_AUDIT"}) 
 */
class Classe extends baseLov
{
    use IsDeletedTrait;

    public const PSMF = 'psmf';
    public const CLIENT = 'client';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity=Variable::class, mappedBy="classes")
     * @ORM\OrderBy({"balise" = "ASC"})
     */    
    protected $variables;

    /**
     * @ORM\ManyToMany(targetEntity=Section::class, inversedBy="classes")
     */
    protected $sections;

    public function __construct()
    {
        parent::__construct();
        $this->variables = new ArrayCollection();
        $this->sections = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function computeCode(SluggerInterface $slugger)
    {
        if (!$this->code || '-' === $this->code) {
            $this->code = (string) $slugger->slug((string)$this, '_')->lower();
        }
    }

    /**
     * @return Collection|Variable[]
     */
    public function getVariables(): Collection
    {
        return $this->variables;
    }

    public function addVariable(Variable $variable): self
    {
        if (!$this->variables->contains($variable)) {
            $this->variables[] = $variable;
            $variable->setClasse($this);
        }

        return $this;
    }

    public function removeVariable(Variable $variable): self
    {
        if ($this->variables->removeElement($variable)) {
            $variable->removeClasse($this);
        }

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
            $section->addClasse($this);
        }

        return $this;
    }

    public function removeSection(Section $section): self
    {
        if ($this->sections->removeElement($section)) {
            $section->removeClasse($this);
        }

        return $this;
    }

    // fonction utile
    private $pSMFs = [];
    private $correspondances = 0;

    public function getCorrespondances(): int
    {
        foreach ($this->getVariables() as $variable) {
            $this->correspondances += count($variable->getCorrespondances());
        }
        return $this->correspondances;
    }     
    
    public function getPSMFs(): array 
    {
        foreach ($this->getVariables() as $variable) {
            foreach ($variable->getCorrespondances() as $correspondance) {
                if (!in_array($correspondance->getPsmf(), $this->pSMFs)) {
                    $this->pSMFs[] = $correspondance->getPsmf();
                }
            }
        }
        return $this->pSMFs;
    }    
}
