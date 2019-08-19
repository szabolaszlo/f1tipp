<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 01. 07.
 * Time: 13:19
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="`message_wall`")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class Message
{
    /**
     * @ORM\Column(name="id", type="integer", length=11, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\Column(name="content", type="text", nullable=false)
     */
    protected $content;

    /**
     * @ORM\Column(name="date_time", type="datetime", nullable=true)
     */
    protected $dateTime;

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
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
    
    /**
     * @return mixed
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * @param mixed $dateTime
     */
    public function setDateTime($dateTime)
    {
        $this->dateTime = $dateTime;
    }
}
