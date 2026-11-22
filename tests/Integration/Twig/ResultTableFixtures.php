<?php

namespace App\Tests\Integration\Twig;

/**
 * Egyszerű stub objektumok a result_table sablonok rendereléséhez (Twig csak a getter-eket hívja).
 */
trait ResultTableFixtures
{
    private function event(string $type, string $name = 'ABU DHABI', string $date = '2025-12-07 14:00:00'): object
    {
        return new class($type, $name, $date) {
            private $type; private $name; private $date;
            public function __construct($type, $name, $date) { $this->type = $type; $this->name = $name; $this->date = new \DateTime($date); }
            public function getType() { return $this->type; }
            public function getName() { return $this->name; }
            public function getDateTime() { return $this->date; }
            public function getId() { return 54; }
        };
    }

    private function attr(string $key, string $value, ?string $class = null): object
    {
        return new class($key, $value, $class) {
            private $key; private $value; private $class;
            public function __construct($key, $value, $class) { $this->key = $key; $this->value = $value; $this->class = $class; }
            public function getKey() { return $this->key; }
            public function getValue() { return $this->value; }
            public function getClass() { return $this->class; }
        };
    }

    /** @param array<int, array{0: string, 1: ?string}> $values érték + bet osztály, sorrendben p1.. majd kérdések */
    private function attrs(array $values, int $positions): array
    {
        $out = [];
        foreach ($values as $i => [$value, $class]) {
            $key = $i < $positions ? 'p' . ($i + 1) : 'sc';
            $out[] = $this->attr($key, $value, $class);
        }
        return $out;
    }

    private function bet(string $userName, int $points, array $attributes): object
    {
        return new class($userName, $points, $attributes) {
            private $user; private $points; private $attributes;
            public function __construct($userName, $points, $attributes) {
                $this->user = new class($userName) { private $n; public function __construct($n) { $this->n = $n; } public function getName() { return $this->n; } };
                $this->points = $points; $this->attributes = $attributes;
            }
            public function getUser() { return $this->user; }
            public function getPointSummary() { return $this->points; }
            public function getAttributes() { return $this->attributes; }
        };
    }

    private function result(object $event, array $attributes): object
    {
        return new class($event, $attributes) {
            private $event; private $attributes;
            public function __construct($event, $attributes) { $this->event = $event; $this->attributes = $attributes; }
            public function getEvent() { return $this->event; }
            public function getAttributes() { return $this->attributes; }
        };
    }
}
