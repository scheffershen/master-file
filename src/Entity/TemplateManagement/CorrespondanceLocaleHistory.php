<?php

namespace App\Entity\TemplateManagement;

use App\Entity\PSMFManagement\PSMFHistory;
use App\Traits\ActorTrait;
use App\Traits\DateTrait;
use App\Traits\IsMajeurTrait;
use App\Traits\ReasonTrait;
use App\Entity\PSMFManagement\PSMF;
use App\Repository\TemplateManagement\CorrespondanceLocaleHistoryRepository;
use DH\DoctrineAuditBundle\Annotation as Audit;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CorrespondanceLocaleHistoryRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @Audit\Auditable
 * @Audit\Security(view={"ROLE_AUDIT"})     
 */
class CorrespondanceLocaleHistory
{
    use ActorTrait;
    use DateTrait;
    use IsMajeurTrait;
    use ReasonTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=PSMF::class, inversedBy="correspondanceLocaleHistories")
     * @ORM\JoinColumn(nullable=false)
     */
    private $psmf;

    /**
     * @ORM\ManyToMany(targetEntity=Correspondance::class)
     * @ORM\OrderBy({"variable" = "ASC"})
     */
    private $correspondances;

    /**
     * @ORM\OneToMany(targetEntity=PSMFHistory::class, mappedBy="correspondanceLocale")
     */
    private $pSMFHistories;

    public function __construct()
    {
        $this->correspondances = new ArrayCollection();
        $this->pSMFHistories = new ArrayCollection();
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
        }

        return $this;
    }

    public function removeCorrespondance(Correspondance $correspondance): self
    {
        $this->correspondances->removeElement($correspondance);

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
            $pSMFHistory->setCorrespondanceLocale($this);
        }

        return $this;
    }

    public function removePSMFHistory(PSMFHistory $pSMFHistory): self
    {
        if ($this->pSMFHistories->removeElement($pSMFHistory)) {
            // set the owning side to null (unless already changed)
            if ($pSMFHistory->getCorrespondanceLocale() === $this) {
                $pSMFHistory->setCorrespondanceLocale(null);
            }
        }

        return $this;
    }
}
