<?php

namespace App\Tests\Integration\Cache;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Az e2e környezet saját FileCache mappát kap, a dev/prod a régi, közös mappát használja.
 */
class FileCacheDirTest extends KernelTestCase
{
    public function testDevUsesSharedDir(): void
    {
        self::bootKernel(['environment' => 'dev', 'debug' => true]);

        $dir = self::$kernel->getContainer()->getParameter('app.file_cache_dir');

        $this->assertStringEndsWith('/var/cache/file-cache', $dir);
    }

    public function testE2eUsesEnvCacheDir(): void
    {
        self::bootKernel(['environment' => 'e2e', 'debug' => true]);

        $dir = self::$kernel->getContainer()->getParameter('app.file_cache_dir');

        $this->assertStringEndsWith('/var/cache/e2e/file-cache', $dir);
    }
}
