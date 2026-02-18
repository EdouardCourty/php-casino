<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Tests\Unit\Blackjack\Model;

use Ecourty\PHPCasino\Blackjack\Exception\InvalidShoeException;
use Ecourty\PHPCasino\Blackjack\Model\GameRules;
use PHPUnit\Framework\TestCase;

final class GameRulesTest extends TestCase
{
    public function testStandardRules(): void
    {
        $rules = GameRules::standard();

        $this->assertSame(6, $rules->deckCount);
        $this->assertFalse($rules->dealerHitsOnSoft17);
        $this->assertSame(1.5, $rules->blackjackPayout);
        $this->assertTrue($rules->doubleAfterSplitAllowed);
        $this->assertTrue($rules->surrenderAllowed);
        $this->assertTrue($rules->insuranceAllowed);
        $this->assertSame(0.75, $rules->shoePenetration);
    }

    public function testEuropeanRules(): void
    {
        $rules = GameRules::european();

        $this->assertSame(6, $rules->deckCount);
        $this->assertFalse($rules->dealerHitsOnSoft17);
        $this->assertFalse($rules->doubleAfterSplitAllowed);
        $this->assertFalse($rules->surrenderAllowed);
        $this->assertTrue($rules->insuranceAllowed);
    }

    public function testVegasRules(): void
    {
        $rules = GameRules::vegas();

        $this->assertSame(6, $rules->deckCount);
        $this->assertTrue($rules->dealerHitsOnSoft17);
        $this->assertTrue($rules->doubleAfterSplitAllowed);
        $this->assertTrue($rules->surrenderAllowed);
    }

    public function testSingleDeckRules(): void
    {
        $rules = GameRules::singleDeck();

        $this->assertSame(1, $rules->deckCount);
        $this->assertFalse($rules->dealerHitsOnSoft17);
        $this->assertFalse($rules->doubleAfterSplitAllowed);
        $this->assertFalse($rules->surrenderAllowed);
        $this->assertSame(0.5, $rules->shoePenetration);
    }

    public function testCustomRules(): void
    {
        $rules = new GameRules(
            deckCount: 8,
            dealerHitsOnSoft17: true,
            blackjackPayout: 1.2,
            doubleAfterSplitAllowed: false,
            surrenderAllowed: false,
            insuranceAllowed: false,
            shoePenetration: 0.6,
        );

        $this->assertSame(8, $rules->deckCount);
        $this->assertTrue($rules->dealerHitsOnSoft17);
        $this->assertSame(1.2, $rules->blackjackPayout);
        $this->assertFalse($rules->doubleAfterSplitAllowed);
        $this->assertFalse($rules->surrenderAllowed);
        $this->assertFalse($rules->insuranceAllowed);
        $this->assertSame(0.6, $rules->shoePenetration);
    }

    public function testThrowsExceptionForInvalidDeckCount(): void
    {
        $this->expectException(InvalidShoeException::class);
        $this->expectExceptionMessage('Invalid deck count: 0');

        new GameRules(deckCount: 0);
    }

    public function testThrowsExceptionForInvalidPenetration(): void
    {
        $this->expectException(InvalidShoeException::class);
        $this->expectExceptionMessage('Invalid penetration: 1.5');

        new GameRules(shoePenetration: 1.5);
    }

    public function testThrowsExceptionForNegativeBlackjackPayout(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Blackjack payout must be positive');

        new GameRules(blackjackPayout: -1.5);
    }

    public function testGetDealerStandThreshold(): void
    {
        $rules = GameRules::standard();

        $this->assertSame(17, $rules->getDealerStandThreshold());
    }
}
