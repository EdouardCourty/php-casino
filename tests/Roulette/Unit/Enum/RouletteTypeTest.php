<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Tests\Roulette\Unit\Enum;

use Ecourty\PHPCasino\Roulette\Enum\RouletteNumber;
use Ecourty\PHPCasino\Roulette\Enum\RouletteType;
use PHPUnit\Framework\TestCase;

class RouletteTypeTest extends TestCase
{
    public function testHasDoubleZeroReturnsTrueForAmerican(): void
    {
        $this->assertTrue(RouletteType::AMERICAN->hasDoubleZero());
    }

    public function testHasDoubleZeroReturnsFalseForEuropean(): void
    {
        $this->assertFalse(RouletteType::EUROPEAN->hasDoubleZero());
    }

    public function testGetNumberCountReturns37ForEuropean(): void
    {
        $this->assertSame(37, RouletteType::EUROPEAN->getNumberCount());
    }

    public function testGetNumberCountReturns38ForAmerican(): void
    {
        $this->assertSame(38, RouletteType::AMERICAN->getNumberCount());
    }

    public function testGetNumbersReturnsEuropeanNumbers(): void
    {
        $numbers = RouletteType::EUROPEAN->getNumbers();

        $this->assertCount(37, $numbers);
        $this->assertContains(RouletteNumber::ZERO, $numbers);
        $this->assertNotContains(RouletteNumber::DOUBLE_ZERO, $numbers);
    }

    public function testGetNumbersReturnsAmericanNumbers(): void
    {
        $numbers = RouletteType::AMERICAN->getNumbers();

        $this->assertCount(38, $numbers);
        $this->assertContains(RouletteNumber::ZERO, $numbers);
        $this->assertContains(RouletteNumber::DOUBLE_ZERO, $numbers);
    }
}
