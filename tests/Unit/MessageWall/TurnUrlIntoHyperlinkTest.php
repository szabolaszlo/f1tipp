<?php

namespace App\Tests\Unit\MessageWall;

use App\Controller\Module\MessageWall;
use PHPUnit\Framework\TestCase;

class TurnUrlIntoHyperlinkTest extends TestCase
{
    private function link(string $text): string
    {
        $controller = (new \ReflectionClass(MessageWall::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod(MessageWall::class, 'turnUrlIntoHyperlink');

        // a mentés ugyanígy, előbb escape-eli a felhasználó szövegét
        return $method->invoke($controller, htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    public function testSingleUrlBecomesLink(): void
    {
        $this->assertSame(
            'nézd: <a href="https://f1vilag.hu/hir" title="https://f1vilag.hu/hir" target="_blank">https://f1vilag.hu/hir</a>',
            $this->link('nézd: https://f1vilag.hu/hir')
        );
    }

    public function testUrlWithoutSchemeGetsHttp(): void
    {
        $this->assertSame(
            '<a href="http://www.f1vilag.hu" title="www.f1vilag.hu" target="_blank">http://www.f1vilag.hu</a>',
            $this->link('www.f1vilag.hu')
        );
    }

    public function testRepeatedUrlIsNotNestedIntoThePreviousLink(): void
    {
        $html = $this->link('http://a.hu/x/onmouseover=alert(1)// http://a.hu/x/onmouseover=alert(1)//');

        $this->assertSame(2, substr_count($html, '<a href='));
        $this->assertStringNotContainsString('href="<a', $html);
        $this->assertStringNotContainsString('title="<a', $html);
    }

    public function testUrlPrefixOfAnotherIsNotNested(): void
    {
        $html = $this->link('http://a.hu/x http://a.hu/x/y');

        $this->assertSame(2, substr_count($html, '<a href='));
        $this->assertStringNotContainsString('"<a', $html);
    }

    public function testHtmlStaysEscaped(): void
    {
        $this->assertSame('&lt;script&gt;alert(1)&lt;/script&gt;', $this->link('<script>alert(1)</script>'));
    }
}
