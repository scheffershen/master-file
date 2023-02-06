<?php

namespace App\Entity\QualiosManagement;

use App\Entity\LovManagement\QualiosStatus;
use App\Entity\LovManagement\QualiosType;
use App\Repository\QualiosManagement\DocumentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DocumentRepository::class)
 */
class Document
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $reference;

    /**
     * @ORM\Column(type="integer")
     */
    private $version;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="date")
     */
    private $activityDate;

    /**
     * @ORM\Column(type="date")
     */
    private $createDate;

    /**
     * @ORM\Column(type="date")
     */
    private $validityDate;

    /**
     * @ORM\Column(type="text")
     */
    private $natureOfChange;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $object;

    /**
     * @ORM\Column(type="boolean")
     */
    private $simpleOrDynamic;

    /**
     * @ORM\Column(type="date")
     */
    private $archiveDate;

    /**
     * @ORM\Column(type="date")
     */
    private $reviewDate;

    /**
     * @ORM\Column(type="integer")
     */
    private $replayDate;

    /**
     * @ORM\Column(type="date")
     */
    private $submitDate;

    /**
     * @ORM\Column(type="integer")
     */
    private $signDelay;

    /**
     * @ORM\Column(type="boolean")
     */
    private $lateSignAlert;

    /**
     * @ORM\Column(type="boolean")
     */
    private $formationAccessibility;

    /**
     * @ORM\Column(type="integer")
     */
    private $actionWhenArchive;

    /**
     * @ORM\Column(type="integer")
     */
    private $archivePeriod;

    /**
     * @ORM\Column(type="boolean")
     */
    private $archivePeriodAlert;

    /**
     * @ORM\Column(type="boolean")
     */
    private $reviewAlert;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $docRefQualifForm;

    /**
     * @ORM\Column(type="boolean")
     */
    private $rappelQualifForm;

    /**
     * @ORM\Column(type="integer")
     */
    private $signByLevel;

    /**
     * @ORM\Column(type="integer")
     */
    private $nbLevel;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $docCode;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $titleComposition;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $testMode;

    /**
     * @ORM\Column(type="boolean")
     */
    private $delayValidity;

    /**
     * @ORM\Column(type="boolean")
     */
    private $delayAR;

    /**
     * @ORM\Column(type="boolean")
     */
    private $attachmentAsBody;

    /**
     * @ORM\ManyToOne(targetEntity=QualiosType::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity=QualiosStatus::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $status;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function setVersion(int $version): self
    {
        $this->version = $version;

        return $this;
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

    public function getActivityDate(): ?\DateTimeInterface
    {
        return $this->activityDate;
    }

    public function setActivityDate(\DateTimeInterface $activityDate): self
    {
        $this->activityDate = $activityDate;

        return $this;
    }

    public function getCreateDate(): ?\DateTimeInterface
    {
        return $this->createDate;
    }

    public function setCreateDate(\DateTimeInterface $createDate): self
    {
        $this->createDate = $createDate;

        return $this;
    }

    public function getValidityDate(): ?\DateTimeInterface
    {
        return $this->validityDate;
    }

    public function setValidityDate(\DateTimeInterface $validityDate): self
    {
        $this->validityDate = $validityDate;

        return $this;
    }

    public function getNatureOfChange(): ?string
    {
        return $this->natureOfChange;
    }

    public function setNatureOfChange(string $natureOfChange): self
    {
        $this->natureOfChange = $natureOfChange;

        return $this;
    }

    public function getObject(): ?string
    {
        return $this->object;
    }

    public function setObject(?string $object): self
    {
        $this->object = $object;

        return $this;
    }

    public function isSimpleOrDynamic(): ?bool
    {
        return $this->simpleOrDynamic;
    }

    public function setSimpleOrDynamic(bool $simpleOrDynamic): self
    {
        $this->simpleOrDynamic = $simpleOrDynamic;

        return $this;
    }

    public function getArchiveDate(): ?\DateTimeInterface
    {
        return $this->archiveDate;
    }

    public function setArchiveDate(\DateTimeInterface $archiveDate): self
    {
        $this->archiveDate = $archiveDate;

        return $this;
    }

    public function getReviewDate(): ?\DateTimeInterface
    {
        return $this->reviewDate;
    }

    public function setReviewDate(\DateTimeInterface $reviewDate): self
    {
        $this->reviewDate = $reviewDate;

        return $this;
    }

    public function getReplayDate(): ?int
    {
        return $this->replayDate;
    }

    public function setReplayDate(int $replayDate): self
    {
        $this->replayDate = $replayDate;

        return $this;
    }

    public function getSubmitDate(): ?\DateTimeInterface
    {
        return $this->submitDate;
    }

    public function setSubmitDate(\DateTimeInterface $submitDate): self
    {
        $this->submitDate = $submitDate;

        return $this;
    }

    public function getSignDelay(): ?int
    {
        return $this->signDelay;
    }

    public function setSignDelay(int $signDelay): self
    {
        $this->signDelay = $signDelay;

        return $this;
    }

    public function isLateSignAlert(): ?bool
    {
        return $this->lateSignAlert;
    }

    public function setLateSignAlert(bool $lateSignAlert): self
    {
        $this->lateSignAlert = $lateSignAlert;

        return $this;
    }

    public function isFormationAccessibility(): ?bool
    {
        return $this->formationAccessibility;
    }

    public function setFormationAccessibility(bool $formationAccessibility): self
    {
        $this->formationAccessibility = $formationAccessibility;

        return $this;
    }

    public function getActionWhenArchive(): ?int
    {
        return $this->actionWhenArchive;
    }

    public function setActionWhenArchive(int $actionWhenArchive): self
    {
        $this->actionWhenArchive = $actionWhenArchive;

        return $this;
    }

    public function getArchivePeriod(): ?int
    {
        return $this->archivePeriod;
    }

    public function setArchivePeriod(int $archivePeriod): self
    {
        $this->archivePeriod = $archivePeriod;

        return $this;
    }

    public function isArchivePeriodAlert(): ?bool
    {
        return $this->archivePeriodAlert;
    }

    public function setArchivePeriodAlert(bool $archivePeriodAlert): self
    {
        $this->archivePeriodAlert = $archivePeriodAlert;

        return $this;
    }

    public function isReviewAlert(): ?bool
    {
        return $this->reviewAlert;
    }

    public function setReviewAlert(bool $reviewAlert): self
    {
        $this->reviewAlert = $reviewAlert;

        return $this;
    }

    public function getDocRefQualifForm(): ?string
    {
        return $this->docRefQualifForm;
    }

    public function setDocRefQualifForm(?string $docRefQualifForm): self
    {
        $this->docRefQualifForm = $docRefQualifForm;

        return $this;
    }

    public function isRappelQualifForm(): ?bool
    {
        return $this->rappelQualifForm;
    }

    public function setRappelQualifForm(bool $rappelQualifForm): self
    {
        $this->rappelQualifForm = $rappelQualifForm;

        return $this;
    }

    public function getSignByLevel(): ?int
    {
        return $this->signByLevel;
    }

    public function setSignByLevel(int $signByLevel): self
    {
        $this->signByLevel = $signByLevel;

        return $this;
    }

    public function getNbLevel(): ?int
    {
        return $this->nbLevel;
    }

    public function setNbLevel(int $nbLevel): self
    {
        $this->nbLevel = $nbLevel;

        return $this;
    }

    public function getDocCode(): ?string
    {
        return $this->docCode;
    }

    public function setDocCode(string $docCode): self
    {
        $this->docCode = $docCode;

        return $this;
    }

    public function getTitleComposition(): ?string
    {
        return $this->titleComposition;
    }

    public function setTitleComposition(?string $titleComposition): self
    {
        $this->titleComposition = $titleComposition;

        return $this;
    }

    public function getTestMode(): ?string
    {
        return $this->testMode;
    }

    public function setTestMode(?string $testMode): self
    {
        $this->testMode = $testMode;

        return $this;
    }

    public function isDelayValidity(): ?bool
    {
        return $this->delayValidity;
    }

    public function setDelayValidity(bool $delayValidity): self
    {
        $this->delayValidity = $delayValidity;

        return $this;
    }

    public function isDelayAR(): ?bool
    {
        return $this->delayAR;
    }

    public function setDelayAR(bool $delayAR): self
    {
        $this->delayAR = $delayAR;

        return $this;
    }

    public function isAttachmentAsBody(): ?bool
    {
        return $this->attachmentAsBody;
    }

    public function setAttachmentAsBody(bool $attachmentAsBody): self
    {
        $this->attachmentAsBody = $attachmentAsBody;

        return $this;
    }

    public function getType(): ?QualiosType
    {
        return $this->type;
    }

    public function setType(?QualiosType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getStatus(): ?QualiosStatus
    {
        return $this->status;
    }

    public function setStatus(?QualiosStatus $status): self
    {
        $this->status = $status;

        return $this;
    }
}
