<?php

namespace App\Controller\Admin;

use App\Cache\FileCache;
use App\Calculator\Calculator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
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
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @param Calculator $calculator
     * @param FileCache $fileCache
     * @return RedirectResponse
     * @throws \Exception
     */
    public function indexAction(Calculator $calculator, FileCache $fileCache)
    {
        $sql = '
        UPDATE bet SET point_summary = NULL;
        UPDATE bet_attribute SET point = NULL, class = NULL;
        UPDATE result SET is_calculated = 0;
        TRUNCATE `alternative_championship`;
        TRUNCATE `trophy`;
        UPDATE `user` SET point_summary = NULL, point_difference = NULL, alternative_point_summary = NULL, alternative_point_difference = NULL;
                ';
        $stmt = $this->getDoctrine()->getConnection()->prepare($sql);
        $stmt->execute();
        $stmt->closeCursor();

        $this->getDoctrine()->getManager()->clear();

        $calculator->calculate();

        foreach ($this->cacheDirs as $cacheDir) {
            $this->renameCacheDir($cacheDir);
        }

        foreach ($this->cacheDirs as $cacheDir) {
            $this->removeCacheDir($cacheDir);
        }

        $fileCache->clearAll();

        $this->addFlash('success', 'admin_maintenance_recalculete_points_success');

        return $this->redirect($this->generateUrl('easyadmin'));
    }

    /**
     * @param $name
     */
    protected function renameCacheDir($name)
    {
        if (null === $this->fileSystem) {
            $this->fileSystem = new Filesystem();
        }

        $this->fileSystem->rename(
            $this->getParameter('kernel.cache_dir') . "/$name",
            $this->getParameter('kernel.cache_dir') . "/$name" . "2"
        );
    }

    /**
     * @param $name
     */
    protected function removeCacheDir($name)
    {
        if (null === $this->fileSystem) {
            $this->fileSystem = new Filesystem();
        }

        $this->fileSystem->remove($this->getParameter('kernel.cache_dir') . "/$name" . "2");
    }
}
