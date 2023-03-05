<?php

namespace App\Controller\Admin;

use App\Cache\FileCache;
use App\Calculator\Calculator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ReCalculateAllPointsController
 * @package App\Controller\Admin
 */
class ReCalculateAllPointsController extends AbstractController
{
    //TODO Add some Loading animation becouse it is a heavy controller

    /**
     * @var Filesystem
     */
    protected $fileSystem = null;

    /**
     * @var string[]
     */
    protected $cacheDirs = [
        'doctrine',
        'twig',
        'pool',
        'profiler'
    ];

    /**
     * @Route(path = "/admin/maintenance/re_calculate_points", name = "re_calculate_points")
     * @param Calculator $calculator
     * @param FileCache $fileCache
     * @return RedirectResponse
     * @throws \Exception
     */
    public function indexAction(EntityManagerInterface $entityManager, Calculator $calculator, FileCache $fileCache)
    {
        $sql = '
        UPDATE bet SET point_summary = NULL;
        UPDATE bet_attribute SET point = NULL, class = NULL;
        UPDATE result SET is_calculated = 0;
        TRUNCATE `alternative_championship`;
        TRUNCATE `trophy`;
        UPDATE `user` SET point_summary = NULL, point_difference = NULL, alternative_point_summary = NULL, alternative_point_difference = NULL;
                ';
        $stmt = $entityManager->getConnection()->prepare($sql);
        $stmt->execute();
        $entityManager->getConnection()->close();

        $entityManager->clear();
        $entityManager->getCache()->evictEntityRegions();

        $calculator->calculate();

        $fileCache->clearAll();

        $this->addFlash('success', 'admin_maintenance_recalculete_points_success');

        return $this->redirect($this->generateUrl('app_admin_dashboard_index'));
    }
}
