<?php

namespace App\Entity\UserManagement;

use App\Security\LoginAttemptSignature;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserManagement\FailedLoginAttemptRepository")
 */
class FailedLoginAttempt
{
    /**
     * The unique auto incremented primary key.
     *
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * The internal primary identity key.
     *
     * @var UuidInterface
     *
     * @ORM\Column(type="uuid")
     *
     */
    protected $uuid;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $signature;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $at;

    /**
     * @var array
     *
     * @ORM\Column(type="json")
     */
    private $extra;

    private function __construct(string $signature, array $extra)
    {
        $this->uuid = Uuid::uuid4();
        $this->signature = $signature;
        $this->at = \DateTime::createFromFormat('U', time());
        $this->extra = $extra;
    }

    /**
     * Returns the primary key identifier.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Returns the internal unique UUID instance.
     */
    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }
    
    public static function createFromRequest(Request $request): self
    {
        $attempt = LoginAttemptSignature::createFromRequest($request);

        return new self(
            $attempt->getSignature(),
            [
                'login' => $attempt->getLogin(),
                'ip' => $attempt->getIp(),
                'user_agent' => $request->headers->get('User-Agent'),
            ]
        );
    }

    public function getSignature(): string
    {
        return $this->signature;
    }
}
