<?php

namespace App\Command\E2e;

/**
 * Az e2e parancsok adatbázist törölnek: csak az e2e kernel-környezetben futhatnak.
 */
final class E2eGuard
{
    public const ENV = 'e2e';

    public static function assertE2e(string $kernelEnv): void
    {
        if ($kernelEnv !== self::ENV) {
            throw new \RuntimeException(sprintf(
                'This command only runs in the e2e environment (current: "%s"). Use --env=e2e.',
                $kernelEnv
            ));
        }
    }
}
