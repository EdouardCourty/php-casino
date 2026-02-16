<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Tests\Roulette\Unit\Enum;

use Ecourty\PHPCasino\Roulette\Enum\BetType;
use PHPUnit\Framework\TestCase;

class BetTypeTest extends TestCase
{
    public function testGetPayoutReturns35ForStraightUp(): void
    {
        $this->assertSame(35, BetType::STRAIGHT_UP->getPayout());
    }

    public function testGetPayoutReturns17ForSplit(): void
    {
        $this->assertSame(17, BetType::SPLIT->getPayout());
    }

    public function testGetPayoutReturns11ForStreet(): void
    {
        $this->assertSame(11, BetType::STREET->getPayout());
    }

    public function testGetPayoutReturns8ForCorner(): void
    {
        $this->assertSame(8, BetType::CORNER->getPayout());
    }

    public function testGetPayoutReturns6ForFiveNumber(): void
    {
        $this->assertSame(6, BetType::FIVE_NUMBER->getPayout());
    }

    public function testGetPayoutReturns5ForLine(): void
    {
        $this->assertSame(5, BetType::LINE->getPayout());
    }

    public function testGetPayoutReturns2ForDozen(): void
    {
        $this->assertSame(2, BetType::DOZEN->getPayout());
    }

    public function testGetPayoutReturns2ForColumn(): void
    {
        $this->assertSame(2, BetType::COLUMN->getPayout());
    }

    public function testGetPayoutReturns1ForEvenMoneyBets(): void
    {
        $this->assertSame(1, BetType::RED->getPayout());
        $this->assertSame(1, BetType::BLACK->getPayout());
        $this->assertSame(1, BetType::EVEN->getPayout());
        $this->assertSame(1, BetType::ODD->getPayout());
        $this->assertSame(1, BetType::LOW->getPayout());
        $this->assertSame(1, BetType::HIGH->getPayout());
    }

    public function testIsInsideBetReturnsTrueForInsideBets(): void
    {
        $this->assertTrue(BetType::STRAIGHT_UP->isInsideBet());
        $this->assertTrue(BetType::SPLIT->isInsideBet());
        $this->assertTrue(BetType::STREET->isInsideBet());
        $this->assertTrue(BetType::CORNER->isInsideBet());
        $this->assertTrue(BetType::FIVE_NUMBER->isInsideBet());
        $this->assertTrue(BetType::LINE->isInsideBet());
    }

    public function testIsInsideBetReturnsFalseForOutsideBets(): void
    {
        $this->assertFalse(BetType::RED->isInsideBet());
        $this->assertFalse(BetType::BLACK->isInsideBet());
        $this->assertFalse(BetType::DOZEN->isInsideBet());
        $this->assertFalse(BetType::COLUMN->isInsideBet());
    }

    public function testIsOutsideBetReturnsTrueForOutsideBets(): void
    {
        $this->assertTrue(BetType::RED->isOutsideBet());
        $this->assertTrue(BetType::BLACK->isOutsideBet());
        $this->assertTrue(BetType::EVEN->isOutsideBet());
        $this->assertTrue(BetType::ODD->isOutsideBet());
        $this->assertTrue(BetType::LOW->isOutsideBet());
        $this->assertTrue(BetType::HIGH->isOutsideBet());
        $this->assertTrue(BetType::DOZEN->isOutsideBet());
        $this->assertTrue(BetType::COLUMN->isOutsideBet());
    }

    public function testIsOutsideBetReturnsFalseForInsideBets(): void
    {
        $this->assertFalse(BetType::STRAIGHT_UP->isOutsideBet());
        $this->assertFalse(BetType::SPLIT->isOutsideBet());
    }

    public function testGetExpectedNumberCountReturnsCorrectValues(): void
    {
        $this->assertSame(1, BetType::STRAIGHT_UP->getExpectedNumberCount());
        $this->assertSame(2, BetType::SPLIT->getExpectedNumberCount());
        $this->assertSame(3, BetType::STREET->getExpectedNumberCount());
        $this->assertSame(4, BetType::CORNER->getExpectedNumberCount());
        $this->assertSame(5, BetType::FIVE_NUMBER->getExpectedNumberCount());
        $this->assertSame(6, BetType::LINE->getExpectedNumberCount());
    }

    public function testGetExpectedNumberCountReturnsNullForOutsideBets(): void
    {
        $this->assertNull(BetType::RED->getExpectedNumberCount());
        $this->assertNull(BetType::BLACK->getExpectedNumberCount());
        $this->assertNull(BetType::DOZEN->getExpectedNumberCount());
        $this->assertNull(BetType::COLUMN->getExpectedNumberCount());
    }
}
