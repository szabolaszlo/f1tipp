<?php

namespace App\Controller\Admin;

use App\Cache\FileCache;
use App\Calculator\Calculator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ClearCacheController
 * @package App\Controller\Admin
 */
class ClearCacheController extends AbstractController
{
    /**
     * @Route(path = "/admin/maintenance/cache_clear", name = "cache_clear")
     * @Security("has_role('ROLE_ADMIN')")
     * @param FileCache $fileCache
     * @return RedirectResponse
     * @throws \Exception
     */
    public function indexAction(FileCache $fileCache)
    {
        $fileCache->clearAll();

        $fs = new Filesystem();
        $fs->remove($this->getParameter('kernel.cache_dir') . '/doctrine');
        $fs->remove($this->getParameter('kernel.cache_dir') . '/twig');
        $fs->remove($this->getParameter('kernel.cache_dir') . '/pools');
        $fs->remove($this->getParameter('kernel.cache_dir') . '/profiler');

        $this->addFlash('success', 'admin_maintenance_clear_cache_success');

        return $this->redirect($this->generateUrl('easyadmin'));
    }
}