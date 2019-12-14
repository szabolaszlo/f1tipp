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
     * @ORM\OneToMany(targetEntity="UserAuthentication", mappedBy="user", cascade={"persist","remove"})
     */
    protected $auth;

    /**
     * @ORM\OneToMany(targetEntity="Trophy", mappedBy="user", cascade={"persist","remove"})
     */
    protected $trophies;

    /**
     * @ORM\OneToMany(targetEntity="UserSkippedNews", mappedBy="user", cascade={"persist","remove"})
     */
    protected $skippedNews;

    /**
     * @ORM\Column(name="timestamp", type="integer", length=11, nullable=false)
     */
    protected $timestamp;

    /**
     * @ORM\Column(name="admin", type="boolean", length=1, nullable=true)
     */
    protected $isAdmin;

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
     * User constructor.
     */
    public function __construct()
    {
        $this->skippedNews = new ArrayCollection();
    }

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
        return (string) $this->username;
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
        return (string) $this->password;
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
     * @return mixed
     */
    public function isAdmin()
    {
        return $this->isAdmin;
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
        $trophies = array();

        /** @var Trophy $trophy */
        foreach ($this->trophies as $trophy) {
            $trophies[$trophy->getType()][] = $trophy;
        }

        return array_replace(array_flip(array('gold', 'silver', 'bronze')), $trophies);
    }

    /**
     * @return mixed
     */
    public function getSkippedNews()
    {
        $skippedInformation = new ArrayCollection();

        /** @var UserSkippedNews $skipped */
        foreach ($this->skippedNews as $skipped) {
            $skippedInformation->add($skipped->getInformation());
        }
        return $skippedInformation;
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
}
