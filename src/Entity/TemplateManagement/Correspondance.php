<?php

namespace App\Entity\TemplateManagement;

use App\Entity\PSMFManagement\PSMF;
use App\Traits\ActorTrait;
use App\Traits\DateTrait;
use App\Traits\ReasonTrait;
use App\Repository\TemplateManagement\CorrespondanceRepository;
use DH\DoctrineAuditBundle\Annotation as Audit;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass=CorrespondanceRepository::class)
 * @ORM\HasLifecycleCallbacks()   
 * @Audit\Auditable
 * @Audit\Security(view={"ROLE_AUDIT"})
 * @Vich\Uploadable  
 */
class Correspondance
{
    public const UM = 'UM';
    public const CLIENT = 'Client';   
    public const LESDEUX = 'Les deux';  

    use ActorTrait;
    use DateTrait;
    use ReasonTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=PSMF::class, inversedBy="correspondances")
     */
    private $psmf;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $valueLocal;

    /**
     * @Vich\UploadableField(mapping="uploads_private", fileNameProperty="valueLocal")
     * @var File
     */
    protected $upload;

    /**
     * @ORM\ManyToOne(targetEntity=Variable::class, inversedBy="correspondances")
     * @ORM\JoinColumn(nullable=false)
     */
    private $variable;

    /**
     * @ORM\ManyToMany(targetEntity=CorrespondanceGlobaleHistory::class, mappedBy="correspondances")
     */
    private $globaleHistorys;

    public function __construct()
    {
        $this->globaleHistorys = new ArrayCollection();
    }

    public function __toString()
    {
        if(is_null($this->valueLocal)) {
            return 'NULL';
        }
        return $this->valueLocal;
    }   

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPsmf(): ?PSMF
    {
        return $this->psmf;
    }

    public function setPsmf(?PSMF $psmf): self
    {
        $this->psmf = $psmf;

        return $this;
    }

    public function getValueLocal(): ?string
    {
        return $this->valueLocal;
    }

    public function setValueLocal(?string $valueLocal): self
    {
        $this->valueLocal = $valueLocal;

        return $this;
    }

    public function getUpload(): ?File
    {
        return $this->upload;
    }

    public function setUpload(File $upload = null): self
    {
        $this->upload = $upload;
        return $this;
    }

    public function getVariable(): ?Variable
    {
        return $this->variable;
    }

    public function setVariable(?Variable $variable): self
    {
        $this->variable = $variable;

        return $this;
    }

    /**
     * @return Collection|globaleHistory[]
     */
    public function getGlobaleHistorys(): Collection
    {
        return $this->globaleHistorys;
    }
    public function addGlobaleHistory(CorrespondanceGlobaleHistory $globaleHistory): self
    {
        if (!$this->globaleHistorys->contains($globaleHistory)) {
            $this->globaleHistorys[] = $globaleHistory;
            $globaleHistory->addCorrespondance($this);
        }
        return $this;
    }
    public function removeGlobaleHistory(CorrespondanceGlobaleHistory $globaleHistory): self
    {
        if ($this->globaleHistorys->contains($globaleHistory)) {
            $this->globaleHistorys->removeElement($globaleHistory);
            $globaleHistory->removeCorrespondance($this);
        }
        return $this;
    }

}
