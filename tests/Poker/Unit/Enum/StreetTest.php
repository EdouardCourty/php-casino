<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Tests\Poker\Unit;

use Ecourty\PHPCasino\Poker\Enum\Street;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Street::class)]
class StreetTest extends TestCase
{
    /**
     */
    #[DataProvider('communityCardsCountProvider')]
    public function testGetCommunityCardsCount(Street $street, int $expected): void
    {
        $this->assertSame($expected, $street->getCommunityCardsCount());
    }

    /**
     * @return array<string, array{0: Street, 1: int}>
     */
    public static function communityCardsCountProvider(): array
    {
        return [
            'Preflop has 0 cards' => [Street::PREFLOP, 0],
            'Flop has 3 cards' => [Street::FLOP, 3],
            'Turn has 4 cards' => [Street::TURN, 4],
            'River has 5 cards' => [Street::RIVER, 5],
            'Showdown has 5 cards' => [Street::SHOWDOWN, 5],
        ];
    }

    /**
     */
    #[DataProvider('nextStreetProvider')]
    public function testGetNextStreet(Street $street, ?Street $expected): void
    {
        $this->assertSame($expected, $street->getNextStreet());
    }

    /**
     * @return array<string, array{0: Street, 1: ?Street}>
     */
    public static function nextStreetProvider(): array
    {
        return [
            'Preflop → Flop' => [Street::PREFLOP, Street::FLOP],
            'Flop → Turn' => [Street::FLOP, Street::TURN],
            'Turn → River' => [Street::TURN, Street::RIVER],
            'River → Showdown' => [Street::RIVER, Street::SHOWDOWN],
            'Showdown → null' => [Street::SHOWDOWN, null],
        ];
    }

    /**
     */
    #[DataProvider('postflopProvider')]
    public function testIsPostflop(Street $street, bool $expected): void
    {
        $this->assertSame($expected, $street->isPostflop());
    }

    /**
     * @return array<string, array{0: Street, 1: bool}>
     */
    public static function postflopProvider(): array
    {
        return [
            'Preflop is not postflop' => [Street::PREFLOP, false],
            'Flop is postflop' => [Street::FLOP, true],
            'Turn is postflop' => [Street::TURN, true],
            'River is postflop' => [Street::RIVER, true],
            'Showdown is postflop' => [Street::SHOWDOWN, true],
        ];
    }

    /**
     */
    #[DataProvider('allowsBettingProvider')]
    public function testAllowsBetting(Street $street, bool $expected): void
    {
        $this->assertSame($expected, $street->allowsBetting());
    }

    /**
     * @return array<string, array{0: Street, 1: bool}>
     */
    public static function allowsBettingProvider(): array
    {
        return [
            'Preflop allows betting' => [Street::PREFLOP, true],
            'Flop allows betting' => [Street::FLOP, true],
            'Turn allows betting' => [Street::TURN, true],
            'River allows betting' => [Street::RIVER, true],
            'Showdown does not allow betting' => [Street::SHOWDOWN, false],
        ];
    }

    /**
     */
    #[DataProvider('cardsToDealProvider')]
    public function testGetCardsToDeal(Street $street, int $expected): void
    {
        $this->assertSame($expected, $street->getCardsToDeal());
    }

    /**
     * @return array<string, array{0: Street, 1: int}>
     */
    public static function cardsToDealProvider(): array
    {
        return [
            'Preflop deals 0 community cards' => [Street::PREFLOP, 0],
            'Flop deals 3 cards' => [Street::FLOP, 3],
            'Turn deals 1 card' => [Street::TURN, 1],
            'River deals 1 card' => [Street::RIVER, 1],
            'Showdown deals 0 cards' => [Street::SHOWDOWN, 0],
        ];
    }

    public function testAllStreetsHaveUniqueValues(): void
    {
        $values = array_map(
            static fn(Street $street): string => $street->value,
            Street::cases()
        );

        $this->assertCount(count(Street::cases()), array_unique($values), 'All street values should be unique');
    }

    public function testStreetProgression(): void
    {
        $street = Street::PREFLOP;

        // Preflop → Flop
        $street = $street->getNextStreet();
        $this->assertSame(Street::FLOP, $street);

        // Flop → Turn
        $street = $street->getNextStreet();
        $this->assertSame(Street::TURN, $street);

        // Turn → River
        $street = $street->getNextStreet();
        $this->assertSame(Street::RIVER, $street);

        // River → Showdown
        $street = $street->getNextStreet();
        $this->assertSame(Street::SHOWDOWN, $street);

        // Showdown → null
        $this->assertNull($street->getNextStreet());
    }
}
