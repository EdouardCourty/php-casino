<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Tests\Poker\Unit;

use Ecourty\PHPCasino\Poker\Model\Card;
use Ecourty\PHPCasino\Poker\Model\PlayerRange;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PlayerRange::class)]
class PlayerRangeTest extends TestCase
{
    public function testFromSpecificHandWithStrings(): void
    {
        $range = PlayerRange::fromSpecificHand(['As', 'Kh']);

        $this->assertSame(1, $range->count());
        $this->assertTrue($range->isSpecificHand());

        $hand = $range->getSpecificHand();
        $this->assertNotNull($hand);
        $this->assertCount(2, $hand);
        $this->assertSame('As', $hand[0]->toString());
        $this->assertSame('Kh', $hand[1]->toString());
    }

    public function testFromSpecificHandWithCards(): void
    {
        $card1 = Card::fromString('As');
        $card2 = Card::fromString('Kh');
        $range = PlayerRange::fromSpecificHand([$card1, $card2]);

        $this->assertSame(1, $range->count());
        $this->assertTrue($range->isSpecificHand());
    }

    public function testFromMultipleHands(): void
    {
        $range = PlayerRange::fromMultipleHands([
            ['As', 'Ah'],
            ['Ks', 'Kh'],
            ['Qs', 'Qh'],
        ]);

        $this->assertSame(3, $range->count());
        $this->assertFalse($range->isSpecificHand());
        $this->assertNull($range->getSpecificHand());
    }

    public function testFromMultipleHandsWithMixedTypes(): void
    {
        $card1 = Card::fromString('As');
        $card2 = Card::fromString('Ah');

        $range = PlayerRange::fromMultipleHands([
            [$card1, $card2],
            ['Ks', 'Kh'],
        ]);

        $this->assertSame(2, $range->count());
    }

    public function testGetSpecificHandReturnsNullForMultipleHands(): void
    {
        $range = PlayerRange::fromMultipleHands([
            ['As', 'Ah'],
            ['Ks', 'Kh'],
        ]);

        $this->assertNull($range->getSpecificHand());
    }
}
