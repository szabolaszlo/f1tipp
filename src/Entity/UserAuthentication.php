<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 19.
 * Time: 11:52
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="`user_auth`")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class UserAuthentication
{
    /**
     * @ORM\Column(name="id", type="integer", length=11, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="auth")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\Column(name="token", type="string", length=32, nullable=true)
     */
    protected $token;

    /**
     * @ORM\Column(name="date_time", type="datetime", nullable=true)
     */
    protected $tokenExpire;

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
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function getTokenExpire()
    {
        return $this->tokenExpire;
    }

    /**
     * @param mixed $tokenExpire
     */
    public function setTokenExpire($tokenExpire)
    {
        $this->tokenExpire = $tokenExpire;
    }
}
