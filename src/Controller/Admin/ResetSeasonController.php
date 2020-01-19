<?php

namespace App\Controller\Admin;

use App\Cache\FileCache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ResetSeasonController
 * @package App\Controller\Admin
 */
class ResetSeasonController extends AbstractController
{
    /**
     * @Route(path = "/admin/maintenance/reset_season", name = "reset_season")
     * @Security("has_role('ROLE_ADMIN')")
     * @param FileCache $fileCache
     * @return RedirectResponse
     * @throws \Exception
     */

    public function indexAction(FileCache $fileCache)
    {
        $sql = '
        DELETE FROM `bet`;
        DELETE FROM `result`;
        TRUNCATE `alternative_championship`;
        TRUNCATE `trophy`;
        UPDATE `user` SET point_summary = NULL, point_difference = NULL, alternative_point_summary = NULL, alternative_point_difference = NULL;
                ';
        $stmt = $this->getDoctrine()->getConnection()->prepare($sql);
        $stmt->execute();
        $stmt->closeCursor();

        $fs = new Filesystem();
        $fs->remove($this->getParameter('kernel.cache_dir') . '/doctrine');
        $fs->remove($this->getParameter('kernel.cache_dir') . '/twig');
        $fs->remove($this->getParameter('kernel.cache_dir') . '/pools');
        $fs->remove($this->getParameter('kernel.cache_dir') . '/profiler');

        $fileCache->clearAll();

        $this->addFlash('success', 'admin_maintenance_reset_season_success');

        return $this->redirect($this->generateUrl('easyadmin'));
    }
}