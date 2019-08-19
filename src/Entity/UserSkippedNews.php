<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 03. 12.
 * Time: 16:12
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="`user_skipped_news`")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class UserSkippedNews
{
    /**
     * @ORM\Column(name="id", type="integer", length=11, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="skippedNews")
     * @ORM\JoinColumn(name="user", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Information")
     * @ORM\JoinColumn(name="information", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $information;

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
    public function getInformation()
    {
        return $this->information;
    }

    /**
     * @param mixed $information
     */
    public function setInformation($information)
    {
        $this->information = $information;
    }
}
