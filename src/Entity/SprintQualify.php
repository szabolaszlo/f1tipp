<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Event")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class SprintQualify extends Event
{
    /**
     * @return string
     */
    public function getType()
    {
        return 'sprint_qualify';
    }
}
