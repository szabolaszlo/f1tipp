<?php

namespace App\Season;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Egyszer használatos, sessionben tárolt megerősítő kód a szezonváltáshoz.
 */
final class ConfirmationCode
{
    private const SESSION_KEY = 'season_reset_code';

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    /**
     * Új kódot állít ki; a korábbi kód érvényét veszti.
     */
    public function issue(): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->requestStack->getSession()->set(self::SESSION_KEY, $code);

        return $code;
    }

    /**
     * Ellenőrzi a beküldött kódot; a tárolt kódot az eredménytől függetlenül törli.
     */
    public function consume(string $submitted): bool
    {
        $stored = $this->requestStack->getSession()->remove(self::SESSION_KEY);

        if (!is_string($stored) || $stored === '' || $submitted === '') {
            return false;
        }

        return hash_equals($stored, $submitted);
    }
}
