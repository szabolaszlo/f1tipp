<?php

namespace App\Cache;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class FileCache
 * @package App\Cache
 */
class FileCache
{
    /**
     * @var FilesystemAdapter
     */
    protected $fileSystemAdapter;

    /**
     * FileCache constructor.
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->fileSystemAdapter = new FilesystemAdapter(
            '',
            0,
            $parameterBag->get('kernel.project_dir') . '/var/cache/file-cache');
    }

    /**
     * @param $key
     * @return |null
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function get($key)
    {
        $cachedItem = $this->fileSystemAdapter->getItem($key);

        if (!$cachedItem->isHit()) {
            return null;
        }

        $cachedValue = $cachedItem->get();

        $unCompressedValue = @gzuncompress($cachedValue);

        if (is_string($unCompressedValue)) {
            return @unserialize($unCompressedValue);
        }

        return null;
    }

    /**
     * @param $key
     * @param $value
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function save($key, array $value)
    {
        $cachedItem = $this->fileSystemAdapter->getItem($key);
        $cachedItem->set(@gzcompress(@serialize($value)));
        $this->fileSystemAdapter->save($cachedItem);
    }

    public function clearAll()
    {
        $this->fileSystemAdapter->clear();
        $this->fileSystemAdapter->commit();
    }
}
