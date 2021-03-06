<?php

namespace App\User\Entity;

use App\Security\Entity\Credential;
use App\Security\Entity\Email;
use BornFree\TacticianDomainEvent\Recorder\ContainsRecordedEvents;
use BornFree\TacticianDomainEvent\Recorder\EventRecorderCapabilities;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User implements ContainsRecordedEvents
{
    use EventRecorderCapabilities;

    public const ROLE_USER = 'ROLE_USER';

    /**
     * @var Uuid
     * @ORM\Id
     * @ORM\Column(type="guid")
     *
     * @Groups({"api_user_register"})
     */
    private $id;


    /**
     * @var FullName
     * @ORM\Embedded(class="App\User\Entity\FullName", columnPrefix=false)
     *
     * @Groups({"api_user_register"})
     */
    private $fullName;

    /**
     * @var Credential
     * @ORM\OneToOne(targetEntity="App\Security\Entity\Credential", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="id", referencedColumnName="id")
     */
    private $credential;

    public function __construct(UserBuilder $builder)
    {
        $this->id = Uuid::uuid4();
        $this->fullName = $builder->fullName();
        $this->credential = new Credential($this->id, $builder->email(), $builder->password(), $this->roles());

        $this->record(new UserWasCreated($this));
    }

    public static function builder(): UserBuilder
    {
        return new UserBuilder();
    }

    public function email(): Email
    {
        return $this->credential->email();
    }

    public function roles(): array
    {
        return [ self::ROLE_USER ];
    }
}