<?php

namespace App\Cache;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * Kulcs-érték tár a Doctrine result cache poolja fölött (PSR-6).
 *
 * A korábbi `Configuration::getResultCacheImpl()` (doctrine/cache, Symfony DoctrineProvider) API-ját
 * adja vissza: a `save()` 0 élettartama lejárat nélküli tárolást jelent, a pozitív korlátoz. A negatív
 * élettartam (a régi API-ban szintén "örökre") itt már lejártnak számít, és nem kerül a cache-be.
 */
class ResultCache
{
    public function __construct(private readonly CacheItemPoolInterface $pool)
    {
    }

    public static function of(EntityManagerInterface $entityManager): self
    {
        return new self($entityManager->getConfiguration()->getResultCache() ?? new ArrayAdapter());
    }

    public function contains(string $key): bool
    {
        return $this->pool->hasItem($key);
    }

    public function fetch(string $key): mixed
    {
        $item = $this->pool->getItem($key);

        return $item->isHit() ? $item->get() : false;
    }

    public function save(string $key, mixed $data, int $lifeTime = 0): bool
    {
        // pl. a szezon utolsó eseménye után számolt "következő esemény" (esemény + 3 nap már elmúlt):
        // örökre eltéve az új szezon felvitele után is a tavalyi hétvégét mutatná
        if ($lifeTime < 0) {
            $this->pool->deleteItem($key);

            return false;
        }

        $item = $this->pool->getItem($key)->set($data);
        if ($lifeTime > 0) {
            $item->expiresAfter($lifeTime);
        }

        return $this->pool->save($item);
    }

    public function delete(string $key): bool
    {
        return $this->pool->deleteItem($key);
    }

    public function deleteAll(): bool
    {
        return $this->pool->clear();
    }
}
