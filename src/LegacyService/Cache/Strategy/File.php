<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 29.
 * Time: 21:14
 */

namespace App\LegacyService\Cache\Strategy;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;

/**
 * Class File
 * @package App\LegacyService\Cache\Strategy
 */
class File implements IStrategy
{
    protected $cacheDir;

    /**
     * File constructor.
     */
    public function __construct()
    {
        $fileSystem = new FilesystemAdapter();
        $this->cacheDir = $_SERVER['DOCUMENT_ROOT'] . '/cache/app';

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir);
        }
    }

    /**
     * @param $id
     * @return bool|string
     */
    public function getCache($id)
    {
        $file = $this->cacheDir . '/' . $id;
        if (is_file($file)) {
            return @gzuncompress(file_get_contents($file));
        }

        return false;
    }

    /**
     * @param $id
     * @param $content
     */
    public function setCache($id, $content)
    {
        $file = $this->cacheDir . '/' . $id;
        file_put_contents($file, @gzcompress($content, 1));
    }

    /**
     * @param $id
     */
    public function removeCache($id)
    {
        $file = $this->cacheDir . '/' . $id;
        if (is_file($file)) {
            unlink($file);
        }
    }
}
