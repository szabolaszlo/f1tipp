<?php

namespace App\Calculator\Provider;

use App\Repository\ChampionshipPointRepository;

/**
 * A bajnoki pontozási tábla (hétvégi helyezés => bajnoki pont), a championship_point táblából,
 * second-level cache-ből. Adminban szerkeszthető ("Bajnoki pontok").
 */
class PointProvider
{
    public function __construct(private readonly ChampionshipPointRepository $repository)
    {
    }

    /**
     * @return int a helyezésért járó bajnoki pont; a táblában nem szereplő helyezés 0
     */
    public function getPlacePoint(int $place): int
    {
        return $this->getPlacePoints()[$place] ?? 0;
    }

    /**
     * @return array<int, int> helyezés => bajnoki pont, helyezés szerint növekvő
     */
    public function getPlacePoints(): array
    {
        return $this->repository->getPlacePoints();
    }
}
