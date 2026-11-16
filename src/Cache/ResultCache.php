<?php

namespace App\Cache;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * Kulcs-érték tár a Doctrine result cache poolja fölött (PSR-6).
 *
 * A korábbi `Configuration::getResultCacheImpl()` (doctrine/cache, Symfony DoctrineProvider) API-ját
 * adja vissza, ugyanazzal a szemantikával: a `save()` élettartama csak pozitív értéknél korlátoz,
 * 0 vagy negatív élettartam lejárat nélküli tárolást jelent.
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
