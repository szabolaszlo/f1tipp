<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 09.
 * Time: 20:36
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Event")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class Qualify extends Event
{

}
