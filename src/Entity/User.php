<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="`user`")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class User implements UserInterface
{
    /**
     * @ORM\Column(name="id", type="integer", length=2, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    //TODO: Duplicate name because legacy stuffs, fix it!
    /**
     * @ORM\Column(name="name", type="string", length=45, nullable=false)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="array")
     */
    private $roles = [];

    /**
     * @ORM\Column(name="password", type="string", length=60, nullable=false)
     */
    protected $password;

    /**
     * @ORM\OneToMany(targetEntity="Trophy", mappedBy="user", cascade={"persist","remove"})
     */
    protected $trophies;

    /**
     * @ORM\Column(name="timestamp", type="integer", length=11, nullable=true)
     */
    protected $timestamp;

    /**
     * @ORM\Column(name="is_alter_champs", type="boolean", nullable=true, options={"default":0})
     */
    protected $isAlterChamps;

    /**
     * @ORM\Column(name="point_summary", type="integer", length=11, nullable=false)
     */
    protected $pointSummary = 0;

    /**
     * @var int
     */
    protected $point = 0;

    /**
     * @ORM\Column(name="point_difference", type="integer", length=11, nullable=true)
     */
    protected $pointDifference = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $alternativePointSummary;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $alternativePointDifference;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string)$this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string)$this->password;
    }

    /**
     * @param string $password
     * @return User
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param mixed $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return int
     */
    public function getPoint()
    {
        return $this->point;
    }

    /**
     * @param int $point
     */
    public function setPoint($point)
    {
        $this->point = $point;
    }

    /**
     * @return string
     */
    public function getPointDifference()
    {
        return $this->pointDifference;
    }

    /**
     * @param string $pointDifference
     */
    public function setPointDifference($pointDifference)
    {
        $this->pointDifference = $pointDifference;
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return (bool)in_array('ROLE_ADMIN', (array)$this->roles);
    }

    /**
     * @return mixed
     */
    public function getTrophies()
    {
        return $this->trophies;
    }

    /**
     * @return array
     */
    public function getPodiumTrophies()
    {
        $trophies = [
            'gold' => null,
            'silver' => null,
            'bronze' => null
        ];

        /** @var Trophy $trophy */
        foreach ($this->trophies as $trophy) {
            $trophies[$trophy->getType()][] = $trophy;
        }

        return $trophies;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return int
     */
    public function getPointSummary(): int
    {
        return $this->pointSummary;
    }

    /**
     * @param int $pointSummary
     */
    public function setPointSummary(int $pointSummary): void
    {
        $this->pointSummary = $pointSummary;
    }

    public function getAlternativePointSummary(): ?int
    {
        return $this->alternativePointSummary;
    }

    public function setAlternativePointSummary(?int $alternativePointSummary): self
    {
        $this->alternativePointSummary = $alternativePointSummary;

        return $this;
    }

    public function getAlternativePointDifference(): ?int
    {
        return $this->alternativePointDifference;
    }

    public function setAlternativePointDifference(?int $alternativePointDifference): self
    {
        $this->alternativePointDifference = $alternativePointDifference;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsAlterChamps()
    {
        return $this->isAlterChamps;
    }

    /**
     * @param mixed $isAlterChamps
     */
    public function setIsAlterChamps($isAlterChamps): void
    {
        $this->isAlterChamps = $isAlterChamps;
    }
}
