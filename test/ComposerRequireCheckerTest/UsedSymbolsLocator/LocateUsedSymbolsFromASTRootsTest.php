<?php

declare(strict_types=1);

namespace ComposerRequireCheckerTest\UsedSymbolsLocator;

use ArrayObject;
use ComposerRequireChecker\UsedSymbolsLocator\LocateUsedSymbolsFromASTRoots;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;

use function file_get_contents;

/** @covers \ComposerRequireChecker\UsedSymbolsLocator\LocateUsedSymbolsFromASTRoots */
final class LocateUsedSymbolsFromASTRootsTest extends TestCase
{
    private LocateUsedSymbolsFromASTRoots $locator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->locator = new LocateUsedSymbolsFromASTRoots();
    }

    public function testNoAsts(): void
    {
        $asts    = [];
        $symbols = $this->locate($asts);

        $this->assertCount(0, $symbols);
    }

    public function testLocate(): void
    {
        $node          = new Class_('Foo');
        $node->extends = new Name('Bar');
        $symbols       = $this->locate([[$node]]);

        $this->assertCount(1, $symbols);
        $this->assertContains('Bar', $symbols);
    }

    public function testInvokeReturnsSymbolsSorted(): void
    {
        $expectedSymbols = [
            'Doctrine\Common\Collections\ArrayCollection',
            'FILTER_VALIDATE_URL',
            'filter_var',
            'Foo\Bar\Baz',
            'libxml_clear_errors',
        ];

        $parserFactory = new ParserFactory();

        $parser = $parserFactory->createForHostVersion();

        /** @psalm-suppress PossiblyFalseArgument */
        $ast1 = $parser->parse(file_get_contents(__DIR__ . '/../../fixtures/unknownSymbols/src/OtherThing.php'));
        /** @psalm-suppress PossiblyFalseArgument */
        $ast2 = $parser->parse(file_get_contents(__DIR__ . '/../../fixtures/unknownSymbols/src/YetAnotherThing.php'));

        $symbols = $this->locate([$ast1, $ast2]);

        $this->assertSame($expectedSymbols, $symbols);
    }

    /**
     * @param array<Node> $asts
     *
     * @return array<string>
     */
    private function locate(array $asts): array
    {
        return ($this->locator)(new ArrayObject($asts));
    }
}
