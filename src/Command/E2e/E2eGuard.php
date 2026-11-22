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

    /**
     * Második védővonal: az env név mellett a ténylegesen csatolt adatbázis nevét is ellenőrizzük,
     * mert egy felülírt DATABASE_URL (valódi env változó, .env.local.php, .env.e2e.local) a dev DB-re mutathat.
     */
    public static function assertE2eDatabase(?string $databaseName): void
    {
        if ($databaseName === null || substr($databaseName, -4) !== '_e2e') {
            throw new \RuntimeException(sprintf(
                'Refusing to touch database "%s": the e2e commands only run against a database whose name ends with "_e2e".',
                (string) $databaseName
            ));
        }
    }
}
