<?php

namespace App\Tests\Integration\Cache;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Az e2e környezet saját FileCache mappát kap, a többi környezet a régi, közös mappát használja.
 * Szándékosan nem bootoljuk a dev/e2e kernelt: az a var/cache/dev és var/cache/e2e cache-t írná
 * (rootként futtatva FPM által nem írható fájlokkal).
 */
class FileCacheDirTest extends KernelTestCase
{
    public function testDefaultIsSharedDir(): void
    {
        self::bootKernel(['debug' => true]);

        $dir = self::$kernel->getContainer()->getParameter('app.file_cache_dir');

        $this->assertSame(self::$kernel->getProjectDir() . '/var/cache/file-cache', $dir);
    }

    public function testE2eOverridesWithEnvCacheDir(): void
    {
        $config = Yaml::parseFile(dirname(__DIR__, 3) . '/config/services_e2e.yaml');

        $this->assertSame('%kernel.cache_dir%/file-cache', $config['parameters']['app.file_cache_dir']);
    }
}
