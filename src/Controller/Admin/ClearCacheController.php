<?php

namespace App\Controller\Admin;

use App\Cache\FileCache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ClearCacheController
 * @package App\Controller\Admin
 */
class ClearCacheController extends AbstractController
{
    /**
     * @Route(path = "/admin/maintenance/cache_clear", name = "cache_clear")
     * @param FileCache $fileCache
     * @return RedirectResponse
     * @throws \Exception
     */
    public function indexAction(FileCache $fileCache)
    {
        $fileCache->clearAll();

        $fs = new Filesystem();

        $fs->rename(
            $this->getParameter('kernel.cache_dir'),
            $this->getParameter('kernel.cache_dir') . '2'
        );

        $fs->remove($this->getParameter('kernel.cache_dir'). '2');

        $this->addFlash('success', 'admin_maintenance_clear_cache_success');
        header("Location: /admin");

        return $this->redirect('/admin');
    }
}
