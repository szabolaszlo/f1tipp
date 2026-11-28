<?php

namespace App\Season;

/**
 * A szezonváltási checklist egy pontja. A státusz: ok / fail (élő ellenőrzés ítélettel),
 * info (élő adat ítélet nélkül), neutral (nincs mit ellenőrizni), manual (kézi emlékeztető).
 */
final class ChecklistItem
{
    public const OK = 'ok';
    public const FAIL = 'fail';
    public const INFO = 'info';
    public const NEUTRAL = 'neutral';
    public const MANUAL = 'manual';

    public function __construct(
        public readonly string $key,
        public readonly string $status,
        public readonly string $label,
        public readonly ?string $detail = null,
    ) {
    }
}
