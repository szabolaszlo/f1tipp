<?php

namespace App\Tests\Unit\Season;

use App\Season\ConfirmationCode;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class ConfirmationCodeTest extends TestCase
{
    private ConfirmationCode $code;

    protected function setUp(): void
    {
        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));
        $stack = new RequestStack();
        $stack->push($request);

        $this->code = new ConfirmationCode($stack);
    }

    public function test_issue_returns_six_digits(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $this->assertMatchesRegularExpression('/^\d{6}$/', $this->code->issue());
        }
    }

    public function test_consume_accepts_issued_code_once(): void
    {
        $issued = $this->code->issue();

        $this->assertTrue($this->code->consume($issued));
        $this->assertFalse($this->code->consume($issued));
    }

    public function test_consume_rejects_wrong_code_and_invalidates(): void
    {
        $issued = $this->code->issue();
        $wrong = $issued === '000000' ? '000001' : '000000';

        $this->assertFalse($this->code->consume($wrong));
        $this->assertFalse($this->code->consume($issued));
    }

    public function test_new_issue_invalidates_previous(): void
    {
        do {
            $a = $this->code->issue();
            $b = $this->code->issue();
        } while ($a === $b);

        $this->assertFalse($this->code->consume($a));
    }

    public function test_consume_rejects_empty(): void
    {
        $this->code->issue();

        $this->assertFalse($this->code->consume(''));
    }

    public function test_consume_without_issue_is_false(): void
    {
        $this->assertFalse($this->code->consume('123456'));
    }
}
