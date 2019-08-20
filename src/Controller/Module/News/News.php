<?php

namespace App\Controller\Module\News;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\UserSkippedNews;

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 03. 12.
 * Time: 10:56
 */
class News extends AbstractController
{
    /**
     * @return mixed
     */
    public function indexAction()
    {
        $user = $this->registry->getUserAuth()->getLoggedUser();

        /** @var ArrayCollection $userSkippedNews */
        $userSkippedNews = ($user && $user->getSkippedNews()) ? $user->getSkippedNews() : new ArrayCollection();

        $informations = (array)$this->entityManager
            ->getRepository('App\Entity\Information')
            ->findBy(array('news' => true));

        foreach ($informations as $key => $information) {
            if ($userSkippedNews->contains($information)) {
                unset($informations[$key]);
            }
        }

        if (!empty($informations)) {
            $this->data['informations'] = $informations;
            $this->data['userToken'] = $this->registry->getUserAuth()->getActualToken();
            return $this->render();
        }
    }

    public function addSkippedNewsAction()
    {
        $informationId = (int)$this->request->getPost('information-id', 0);

        $information = $this->entityManager
            ->getRepository('App\Entity\Information')
            ->find($informationId);

        $user = $this->registry->getUserAuth()->getUserByToken($this->request->getPost('user-token', 0));

        if ($user && $information) {
            $skippedNews = new UserSkippedNews();
            $skippedNews->setInformation($information);
            $skippedNews->setUser($user);

            $this->entityManager->persist($skippedNews);
            $this->entityManager->flush();
        }
    }
}
