<?php

namespace App\Tests\Unit\Display;

use App\Display\DriverName;
use PHPUnit\Framework\TestCase;

class DriverNameTest extends TestCase
{
    /**
     * @dataProvider names
     */
    public function test_splits_full_name_into_given_and_family_name(string $full, string $given, string $family): void
    {
        self::assertSame(['givenName' => $given, 'familyName' => $family], DriverName::split($full));
    }

    public function names(): array
    {
        return [
            'simple' => ['Max Verstappen', 'Max', 'Verstappen'],
            'middle name' => ['Andrea Kimi Antonelli', 'Andrea Kimi', 'Antonelli'],
            'suffix stays with family' => ['Carlos Sainz Jr', 'Carlos', 'Sainz Jr'],
            'suffix with dot' => ['Carlos Sainz Jr.', 'Carlos', 'Sainz Jr.'],
            'accented' => ['Nico Hülkenberg', 'Nico', 'Hülkenberg'],
            'single word' => ['Zhou', '', 'Zhou'],
            'extra whitespace' => ['  Lando   Norris ', 'Lando', 'Norris'],
        ];
    }
}
