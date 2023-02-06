<?php

namespace App\Entity\PSMFManagement;

use App\Entity\LovManagement\ActiviteUM;
use App\Entity\LovManagement\BasePV;
use App\Entity\LovManagement\EntitType;
use App\Entity\LovManagement\LocalQPPV;
use App\Entity\LovManagement\Pays;
use App\Entity\TemplateManagement\Correspondance;
use App\Entity\TemplateManagement\CorrespondanceLocaleHistory;
use App\Entity\UserManagement\Client;
use App\Entity\UserManagement\User;
use App\Traits\ActorTrait;
use App\Traits\DateTrait;
use App\Traits\IsValidTrait;
use App\Traits\IsDeletedTrait;
use App\Traits\ReasonTrait;
use App\Repository\PSMFManagement\PSMFRepository;
use DH\DoctrineAuditBundle\Annotation as Audit;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PSMFRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @Audit\Auditable
 * @Audit\Security(view={"ROLE_AUDIT"})    
 */
class PSMF
{
    public const WORD = 'word';
    public const VALIDATOR = 'validator'; // html-validator for debugging
    public const PDF = 'pdf';
    public const PDF_SIGNE = 'pdfSigne';
    public const HTML = 'html';
    public const NOT_FOUND_IMAGE = 'not-found.png';
    
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
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="pSMFs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $client;

    /**
     * @ORM\ManyToOne(targetEntity=EntitType::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $euqppvEntity;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $eudravigNum;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="euQPPVPSMFs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $euQPPV;

    /**
     * "transformer le FR-PV du PSMF en variables locales"
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $frRPV;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="deputyEUQPPVPSMFs")
     */
    private $deputyEUQPPV;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="contactPvClientPSMFs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $contactPvClient;

    /**
     * @ORM\OneToMany(targetEntity=PublishedDocument::class, mappedBy="psmf", orphanRemoval=true)
     * @ORM\OrderBy({"version" = "DESC"})
     */
    private $publishedDocuments;

    /**
     * @ORM\OneToMany(targetEntity=Correspondance::class, mappedBy="psmf")
     */
    private $correspondances;

    /**
     * @ORM\OneToMany(targetEntity=CorrespondanceLocaleHistory::class, mappedBy="psmf", orphanRemoval=true)
     */
    private $correspondanceLocaleHistories;

    /**
     * @ORM\ManyToMany(targetEntity=PSMFHistory::class, mappedBy="psmfs")
     */
    private $pSMFHistories;

    /**
     * @ORM\ManyToMany(targetEntity=ActiviteUM::class)
     */
    private $activitesUM;

    /**
     * @ORM\ManyToMany(targetEntity=Pays::class)
     */
    private $localQPPVPays; // localQPPVPays == localQPPVOther

    /**
     * @ORM\ManyToMany(targetEntity=LocalQPPV::class)
     */
    private $localQPPVUM;

    /**
     * @ORM\ManyToOne(targetEntity=BasePV::class)
     */
    private $basePV;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $hasOtherPVProviders;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isOldClientBbac;

    public function __construct()
    {
        $this->publishedDocuments = new ArrayCollection();
        $this->correspondances = new ArrayCollection();
        $this->correspondanceLocaleHistories = new ArrayCollection();
        $this->pSMFHistories = new ArrayCollection();
        $this->activitesUM = new ArrayCollection();
        $this->localQPPVPays = new ArrayCollection();
        $this->localQPPVUM = new ArrayCollection();
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

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getEuqppvEntity(): ?EntitType
    {
        return $this->euqppvEntity;
    }

    public function setEuqppvEntity(?EntitType $euqppvEntity): self
    {
        $this->euqppvEntity = $euqppvEntity;

        return $this;
    }

    public function getEudravigNum(): ?string
    {
        return $this->eudravigNum;
    }

    public function setEudravigNum(?string $eudravigNum): self
    {
        $this->eudravigNum = $eudravigNum;

        return $this;
    }

    public function getEuQPPV(): ?User
    {
        return $this->euQPPV;
    }

    public function setEuQPPV(?User $euQPPV): self
    {
        $this->euQPPV = $euQPPV;

        return $this;
    }

    public function getFrRPV(): ?User
    {
        return $this->frRPV;
    }

    public function setFrRPV(?User $frRPV): self
    {
        $this->frRPV = $frRPV;

        return $this;
    }

    public function getDeputyEUQPPV(): ?User
    {
        return $this->deputyEUQPPV;
    }

    public function setDeputyEUQPPV(?User $deputyEUQPPV): self
    {
        $this->deputyEUQPPV = $deputyEUQPPV;

        return $this;
    }

    public function getContactPvClient(): ?User
    {
        return $this->contactPvClient;
    }

    public function setContactPvClient(?User $contactPvClient): self
    {
        $this->contactPvClient = $contactPvClient;

        return $this;
    }

    /**
     * @return Collection|PublishedDocument[]
     */
    public function getPublishedDocuments(): Collection
    {
        return $this->publishedDocuments;
    }

    public function addPublishedDocument(PublishedDocument $publishedDocument): self
    {
        if (!$this->publishedDocuments->contains($publishedDocument)) {
            $this->publishedDocuments[] = $publishedDocument;
            $publishedDocument->setPsmf($this);
        }

        return $this;
    }

    public function removePublishedDocument(PublishedDocument $publishedDocument): self
    {
        if ($this->publishedDocuments->contains($publishedDocument)) {
            $this->publishedDocuments->removeElement($publishedDocument);
            // set the owning side to null (unless already changed)
            if ($publishedDocument->getPsmf() === $this) {
                $publishedDocument->setPsmf(null);
            }
        }

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
            $correspondance->setPsmf($this);
        }

        return $this;
    }

    public function removeCorrespondance(Correspondance $correspondance): self
    {
        if ($this->correspondances->contains($correspondance)) {
            $this->correspondances->removeElement($correspondance);
            // set the owning side to null (unless already changed)
            if ($correspondance->getPsmf() === $this) {
                $correspondance->setPsmf(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CorrespondanceLocaleHistory[]
     */
    public function getCorrespondanceLocaleHistories(): Collection
    {
        return $this->correspondanceLocaleHistories;
    }

    public function addCorrespondanceLocaleHistory(CorrespondanceLocaleHistory $correspondanceLocaleHistory): self
    {
        if (!$this->correspondanceLocaleHistories->contains($correspondanceLocaleHistory)) {
            $this->correspondanceLocaleHistories[] = $correspondanceLocaleHistory;
            $correspondanceLocaleHistory->setPsmf($this);
        }

        return $this;
    }

    public function removeCorrespondanceLocaleHistory(CorrespondanceLocaleHistory $correspondanceLocaleHistory): self
    {
        if ($this->correspondanceLocaleHistories->removeElement($correspondanceLocaleHistory)) {
            // set the owning side to null (unless already changed)
            if ($correspondanceLocaleHistory->getPsmf() === $this) {
                $correspondanceLocaleHistory->setPsmf(null);
            }
        }

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
            $pSMFHistory->addPsmf($this);
        }

        return $this;
    }

    public function removePSMFHistory(PSMFHistory $pSMFHistory): self
    {
        if ($this->pSMFHistories->removeElement($pSMFHistory)) {
            $pSMFHistory->removePsmf($this);
        }

        return $this;
    }   

    /**
    * functions for contollers and templates
    */

    public function hasLastVersion(): ?PublishedDocument
    {
        if ($this->publishedDocuments->count() > 0) {
            return $this->publishedDocuments->first();
        }
        return null;
    }

    public function getLastVersion(): ?PublishedDocument
    {
        return $this->hasLastVersion();
    }

    /**
     * @return Collection|ActiviteUM[]
     */
    public function getActivitesUM(): Collection
    {
        return $this->activitesUM;
    }

    public function addActivitesUM(ActiviteUM $activitesUM): self
    {
        if (!$this->activitesUM->contains($activitesUM)) {
            $this->activitesUM[] = $activitesUM;
        }

        return $this;
    }

    public function removeActivitesUM(ActiviteUM $activitesUM): self
    {
        $this->activitesUM->removeElement($activitesUM);

        return $this;
    }

    public function hasActiviteUM(string $activiteUM): bool
    {        
        if (count($this->activitesUM) > 0)  {
            foreach ($this->activitesUM as $a) {
                if ($a->getCode() == $activiteUM) return true;
            }   
        }

        return false;
    }

    public function getActiviteUM(string $activiteUM): bool
    {        
        if (count($this->activitesUM) > 0)  {
            foreach ($this->activitesUM as $a) {
                if ($a->getCode() == $activiteUM) return $a->getTitle();
            }   
        }

        return '';
    }

    /**
     * @return Collection|Pays[]
     */
    public function getLocalQPPVPays(): Collection
    {
        return $this->localQPPVPays;
    }

    public function addLocalQPPVPay(Pays $localQPPVPay): self
    {
        if (!$this->localQPPVPays->contains($localQPPVPay)) {
            $this->localQPPVPays[] = $localQPPVPay;
        }

        return $this;
    }

    public function removeLocalQPPVPay(Pays $localQPPVPay): self
    {
        $this->localQPPVPays->removeElement($localQPPVPay);

        return $this;
    }

    /**
     * @return Collection|LocalQPPV[]
     */
    public function getLocalQPPVUM(): Collection
    {
        return $this->localQPPVUM;
    }

    public function addLocalQPPVUM(LocalQPPV $localQPPVUM): self
    {
        if (!$this->localQPPVUM->contains($localQPPVUM)) {
            $this->localQPPVUM[] = $localQPPVUM;
        }

        return $this;
    }

    public function removeLocalQPPVUM(LocalQPPV $localQPPVUM): self
    {
        $this->localQPPVUM->removeElement($localQPPVUM);

        return $this;
    }

    public function getBasePV(): ?BasePV
    {
        return $this->basePV;
    }

    public function setBasePV(?BasePV $basePV): self
    {
        $this->basePV = $basePV;

        return $this;
    }

    public function getHasOtherPVProviders(): ?bool
    {
        return $this->hasOtherPVProviders;
    }

    public function setHasOtherPVProviders(?bool $hasOtherPVProviders): self
    {
        $this->hasOtherPVProviders = $hasOtherPVProviders;

        return $this;
    }

    public function getIsOldClientBbac(): ?bool
    {
        return $this->isOldClientBbac;
    }

    public function setIsOldClientBbac(?bool $isOldClientBbac): self
    {
        $this->isOldClientBbac = $isOldClientBbac;

        return $this;
    }


    // for templates
    public function hasLocalQPPVOther(string $pays): bool
    {
        if (!$this->localQPPVPays->isEmpty()) {
            foreach ($this->localQPPVPays as $localQPPV) {
                if ($localQPPV->getCode() == $pays) return true;
            }   
        }

        return false;
    }

    // for templates
    public function hasLocalQPPVUM(string $pays): bool
    {
        if (!$this->localQPPVUM->isEmpty()) {
            foreach ($this->localQPPVUM as $localQPPV) {
                if ($localQPPV->getCode() == $pays) return true;
            }   
        }

        return false;
    }    
}
