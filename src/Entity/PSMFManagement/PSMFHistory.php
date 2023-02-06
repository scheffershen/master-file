<?php

namespace App\Entity\PSMFManagement;

use App\Entity\TemplateManagement\CorrespondanceGlobaleHistory;
use App\Entity\TemplateManagement\CorrespondanceLocaleHistory;
use App\Entity\TemplateManagement\Section;
use App\Entity\TemplateManagement\Variable;
use App\Entity\UserManagement\Client;
use App\Entity\UserManagement\User;
use App\Traits\ActorTrait;
use App\Traits\DateTrait;
use App\Traits\ReasonTrait;
use App\Repository\PSMFManagement\PSMFHistoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PSMFHistoryRepository::class)
 * @ORM\HasLifecycleCallbacks() 
 */
class PSMFHistory
{
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
     * @ORM\Column(type="text")
     */
    private $diffs;

    /**
     * @ORM\ManyToMany(targetEntity=PSMF::class, inversedBy="pSMFHistories")
     */
    private $psmfs;

    /**
     * @ORM\ManyToOne(targetEntity=CorrespondanceGlobaleHistory::class, inversedBy="pSMFHistories")
     */
    private $correspondanceGlobale;

    /**
     * @ORM\ManyToOne(targetEntity=CorrespondanceLocaleHistory::class, inversedBy="pSMFHistories")
     */
    private $correspondanceLocale;

    /**
     * @ORM\ManyToOne(targetEntity=Section::class, inversedBy="pSMFHistories")
     */
    private $section;

    /**
     * @ORM\ManyToOne(targetEntity=Variable::class, inversedBy="pSMFHistories")
     */
    private $variable;

    /**
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="pSMFHistories")
     */
    private $client;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="pSMFHistories")
     */
    private $pvuser;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $ip;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $action;

    public function __construct()
    {
        $this->psmfs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDiffs(): ?array
    {
        return json_decode($this->diffs, true);
    }

    public function setDiffs(string $diffs): self
    {
        $this->diffs = $diffs;

        return $this;
    }

    /**
     * @return Collection|PSMF[]
     */
    public function getPsmfs(): Collection
    {
        return $this->psmfs;
    }

    public function addPsmf(PSMF $psmf): self
    {
        if (!$this->psmfs->contains($psmf)) {
            $this->psmfs[] = $psmf;
        }

        return $this;
    }

    public function removePsmf(PSMF $psmf): self
    {
        $this->psmfs->removeElement($psmf);

        return $this;
    }

    public function getcorrespondanceGlobale(): ?CorrespondanceGlobaleHistory
    {
        return $this->correspondanceGlobale;
    }

    public function setCorrespondanceGlobale(?CorrespondanceGlobaleHistory $correspondanceGlobale): self
    {
        $this->correspondanceGlobale = $correspondanceGlobale;

        return $this;
    }

    public function getCorrespondanceLocale(): ?CorrespondanceLocaleHistory
    {
        return $this->correspondanceLocale;
    }

    public function setCorrespondanceLocale(?CorrespondanceLocaleHistory $correspondanceLocale): self
    {
        $this->correspondanceLocale = $correspondanceLocale;

        return $this;
    }

    public function getSection(): ?Section
    {
        return $this->section;
    }

    public function setSection(?Section $section): self
    {
        $this->section = $section;

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

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getPvuser(): ?User
    {
        return $this->pvuser;
    }

    public function setPvuser(?User $pvuser): self
    {
        $this->pvuser = $pvuser;

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;

        return $this;
    }
}
