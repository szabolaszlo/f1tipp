<?php

namespace App\LegacyService\Feed\Fetcher;

/**
 * Egy hírforrás nyers tartalmának letöltése (élesben HTTP, e2e-ben fixture-fájl).
 */
interface IFetcher
{
    /**
     * @return string|null null, ha a forrás nem érhető el vagy üres
     */
    public function fetch(string $url): ?string;
}
