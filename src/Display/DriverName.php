<?php

namespace App\Display;

/**
 * A Driver.name ("Max Verstappen") szétbontása a TV-grafikás megjelenítéshez:
 * nagybetűs családnév + kisebb keresztnév.
 */
final class DriverName
{
    private const SUFFIXES = ['jr', 'jr.', 'sr', 'sr.', 'ii', 'iii'];

    /**
     * @return array{givenName: string, familyName: string}
     */
    public static function split(string $fullName): array
    {
        $parts = preg_split('/\s+/u', trim($fullName), -1, PREG_SPLIT_NO_EMPTY);

        if (count($parts) <= 1) {
            return ['givenName' => '', 'familyName' => $parts[0] ?? ''];
        }

        $familyLength = 1;
        if (count($parts) > 2 && in_array(mb_strtolower(end($parts)), self::SUFFIXES, true)) {
            $familyLength = 2;
        }

        return [
            'givenName' => implode(' ', array_slice($parts, 0, -$familyLength)),
            'familyName' => implode(' ', array_slice($parts, -$familyLength)),
        ];
    }
}
