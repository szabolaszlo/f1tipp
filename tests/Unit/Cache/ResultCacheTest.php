<?php

namespace App\Tests\Unit\Cache;

use App\Cache\ResultCache;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class ResultCacheTest extends TestCase
{
    public function testZeroLifeTimeIsStoredWithoutExpiry(): void
    {
        $cache = new ResultCache(new ArrayAdapter());

        $this->assertTrue($cache->save('key', 'value'));
        $this->assertTrue($cache->contains('key'));
        $this->assertSame('value', $cache->fetch('key'));
    }

    public function testNegativeLifeTimeIsNotStored(): void
    {
        $cache = new ResultCache(new ArrayAdapter());

        $this->assertFalse($cache->save('key', 'value', -60));
        $this->assertFalse($cache->contains('key'));
    }

    public function testNegativeLifeTimeDropsThePreviousValue(): void
    {
        $cache = new ResultCache(new ArrayAdapter());
        $cache->save('key', 'old');

        $cache->save('key', 'new', -1);

        $this->assertFalse($cache->contains('key'));
    }
}
