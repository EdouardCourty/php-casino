<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Tests\Roulette\Unit\Enum;

use Ecourty\PHPCasino\Roulette\Enum\RouletteNumber;
use PHPUnit\Framework\TestCase;

class RouletteNumberTest extends TestCase
{
    public function testGetValueReturnsIntForRegularNumbers(): void
    {
        $this->assertSame(1, RouletteNumber::ONE->getValue());
        $this->assertSame(18, RouletteNumber::EIGHTEEN->getValue());
        $this->assertSame(36, RouletteNumber::THIRTY_SIX->getValue());
    }

    public function testGetValueReturnsZeroForZero(): void
    {
        $this->assertSame(0, RouletteNumber::ZERO->getValue());
    }

    public function testGetValueReturnsStringForDoubleZero(): void
    {
        $this->assertSame('00', RouletteNumber::DOUBLE_ZERO->getValue());
    }

    public function testIsZeroReturnsTrueForZeroAndDoubleZero(): void
    {
        $this->assertTrue(RouletteNumber::ZERO->isZero());
        $this->assertTrue(RouletteNumber::DOUBLE_ZERO->isZero());
    }

    public function testIsZeroReturnsFalseForRegularNumbers(): void
    {
        $this->assertFalse(RouletteNumber::ONE->isZero());
        $this->assertFalse(RouletteNumber::EIGHTEEN->isZero());
    }

    public function testIsRedReturnsTrueForRedNumbers(): void
    {
        $redNumbers = [
            RouletteNumber::ONE, RouletteNumber::THREE, RouletteNumber::FIVE,
            RouletteNumber::SEVEN, RouletteNumber::NINE, RouletteNumber::TWELVE,
            RouletteNumber::FOURTEEN, RouletteNumber::SIXTEEN, RouletteNumber::EIGHTEEN,
            RouletteNumber::NINETEEN, RouletteNumber::TWENTY_ONE, RouletteNumber::TWENTY_THREE,
            RouletteNumber::TWENTY_FIVE, RouletteNumber::TWENTY_SEVEN, RouletteNumber::THIRTY,
            RouletteNumber::THIRTY_TWO, RouletteNumber::THIRTY_FOUR, RouletteNumber::THIRTY_SIX,
        ];

        foreach ($redNumbers as $number) {
            $this->assertTrue($number->isRed(), "Number {$number->value} should be red");
        }
    }

    public function testIsRedReturnsFalseForBlackAndGreenNumbers(): void
    {
        $this->assertFalse(RouletteNumber::ZERO->isRed());
        $this->assertFalse(RouletteNumber::DOUBLE_ZERO->isRed());
        $this->assertFalse(RouletteNumber::TWO->isRed());
        $this->assertFalse(RouletteNumber::FOUR->isRed());
    }

    public function testIsBlackReturnsTrueForBlackNumbers(): void
    {
        $blackNumbers = [
            RouletteNumber::TWO, RouletteNumber::FOUR, RouletteNumber::SIX,
            RouletteNumber::EIGHT, RouletteNumber::TEN, RouletteNumber::ELEVEN,
            RouletteNumber::THIRTEEN, RouletteNumber::FIFTEEN, RouletteNumber::SEVENTEEN,
            RouletteNumber::TWENTY, RouletteNumber::TWENTY_TWO, RouletteNumber::TWENTY_FOUR,
            RouletteNumber::TWENTY_SIX, RouletteNumber::TWENTY_EIGHT, RouletteNumber::TWENTY_NINE,
            RouletteNumber::THIRTY_ONE, RouletteNumber::THIRTY_THREE, RouletteNumber::THIRTY_FIVE,
        ];

        foreach ($blackNumbers as $number) {
            $this->assertTrue($number->isBlack(), "Number {$number->value} should be black");
        }
    }

    public function testIsBlackReturnsFalseForRedAndGreenNumbers(): void
    {
        $this->assertFalse(RouletteNumber::ZERO->isBlack());
        $this->assertFalse(RouletteNumber::DOUBLE_ZERO->isBlack());
        $this->assertFalse(RouletteNumber::ONE->isBlack());
        $this->assertFalse(RouletteNumber::THREE->isBlack());
    }

    public function testIsGreenReturnsTrueForZeros(): void
    {
        $this->assertTrue(RouletteNumber::ZERO->isGreen());
        $this->assertTrue(RouletteNumber::DOUBLE_ZERO->isGreen());
    }

    public function testIsGreenReturnsFalseForRegularNumbers(): void
    {
        $this->assertFalse(RouletteNumber::ONE->isGreen());
        $this->assertFalse(RouletteNumber::EIGHTEEN->isGreen());
    }

    public function testExactly18RedNumbers(): void
    {
        $redCount = 0;
        foreach (RouletteNumber::europeanNumbers() as $number) {
            if ($number->isRed()) {
                $redCount++;
            }
        }

        $this->assertSame(18, $redCount);
    }

    public function testExactly18BlackNumbers(): void
    {
        $blackCount = 0;
        foreach (RouletteNumber::europeanNumbers() as $number) {
            if ($number->isBlack()) {
                $blackCount++;
            }
        }

        $this->assertSame(18, $blackCount);
    }

    public function testIsEvenReturnsTrueForEvenNumbers(): void
    {
        $this->assertTrue(RouletteNumber::TWO->isEven());
        $this->assertTrue(RouletteNumber::FOUR->isEven());
        $this->assertTrue(RouletteNumber::EIGHTEEN->isEven());
        $this->assertTrue(RouletteNumber::THIRTY_SIX->isEven());
    }

    public function testIsEvenReturnsFalseForOddNumbers(): void
    {
        $this->assertFalse(RouletteNumber::ONE->isEven());
        $this->assertFalse(RouletteNumber::THREE->isEven());
        $this->assertFalse(RouletteNumber::THIRTY_FIVE->isEven());
    }

    public function testIsEvenReturnsFalseForZeros(): void
    {
        $this->assertFalse(RouletteNumber::ZERO->isEven());
        $this->assertFalse(RouletteNumber::DOUBLE_ZERO->isEven());
    }

    public function testIsOddReturnsTrueForOddNumbers(): void
    {
        $this->assertTrue(RouletteNumber::ONE->isOdd());
        $this->assertTrue(RouletteNumber::THREE->isOdd());
        $this->assertTrue(RouletteNumber::THIRTY_FIVE->isOdd());
    }

    public function testIsOddReturnsFalseForEvenNumbers(): void
    {
        $this->assertFalse(RouletteNumber::TWO->isOdd());
        $this->assertFalse(RouletteNumber::FOUR->isOdd());
        $this->assertFalse(RouletteNumber::THIRTY_SIX->isOdd());
    }

    public function testIsOddReturnsFalseForZeros(): void
    {
        $this->assertFalse(RouletteNumber::ZERO->isOdd());
        $this->assertFalse(RouletteNumber::DOUBLE_ZERO->isOdd());
    }

    public function testIsLowReturnsTrueForNumbersOneToEighteen(): void
    {
        $this->assertTrue(RouletteNumber::ONE->isLow());
        $this->assertTrue(RouletteNumber::NINE->isLow());
        $this->assertTrue(RouletteNumber::EIGHTEEN->isLow());
    }

    public function testIsLowReturnsFalseForHighNumbers(): void
    {
        $this->assertFalse(RouletteNumber::NINETEEN->isLow());
        $this->assertFalse(RouletteNumber::TWENTY_FIVE->isLow());
        $this->assertFalse(RouletteNumber::THIRTY_SIX->isLow());
    }

    public function testIsLowReturnsFalseForZeros(): void
    {
        $this->assertFalse(RouletteNumber::ZERO->isLow());
        $this->assertFalse(RouletteNumber::DOUBLE_ZERO->isLow());
    }

    public function testIsHighReturnsTrueForNumbersNineteenToThirtySix(): void
    {
        $this->assertTrue(RouletteNumber::NINETEEN->isHigh());
        $this->assertTrue(RouletteNumber::TWENTY_FIVE->isHigh());
        $this->assertTrue(RouletteNumber::THIRTY_SIX->isHigh());
    }

    public function testIsHighReturnsFalseForLowNumbers(): void
    {
        $this->assertFalse(RouletteNumber::ONE->isHigh());
        $this->assertFalse(RouletteNumber::NINE->isHigh());
        $this->assertFalse(RouletteNumber::EIGHTEEN->isHigh());
    }

    public function testIsHighReturnsFalseForZeros(): void
    {
        $this->assertFalse(RouletteNumber::ZERO->isHigh());
        $this->assertFalse(RouletteNumber::DOUBLE_ZERO->isHigh());
    }

    public function testGetDozenReturnsFirstDozen(): void
    {
        $this->assertSame(1, RouletteNumber::ONE->getDozen());
        $this->assertSame(1, RouletteNumber::SIX->getDozen());
        $this->assertSame(1, RouletteNumber::TWELVE->getDozen());
    }

    public function testGetDozenReturnsSecondDozen(): void
    {
        $this->assertSame(2, RouletteNumber::THIRTEEN->getDozen());
        $this->assertSame(2, RouletteNumber::EIGHTEEN->getDozen());
        $this->assertSame(2, RouletteNumber::TWENTY_FOUR->getDozen());
    }

    public function testGetDozenReturnsThirdDozen(): void
    {
        $this->assertSame(3, RouletteNumber::TWENTY_FIVE->getDozen());
        $this->assertSame(3, RouletteNumber::THIRTY->getDozen());
        $this->assertSame(3, RouletteNumber::THIRTY_SIX->getDozen());
    }

    public function testGetDozenReturnsNullForZeros(): void
    {
        $this->assertNull(RouletteNumber::ZERO->getDozen());
        $this->assertNull(RouletteNumber::DOUBLE_ZERO->getDozen());
    }

    public function testGetColumnReturnsFirstColumn(): void
    {
        $this->assertSame(1, RouletteNumber::ONE->getColumn());
        $this->assertSame(1, RouletteNumber::FOUR->getColumn());
        $this->assertSame(1, RouletteNumber::SEVEN->getColumn());
        $this->assertSame(1, RouletteNumber::THIRTY_FOUR->getColumn());
    }

    public function testGetColumnReturnsSecondColumn(): void
    {
        $this->assertSame(2, RouletteNumber::TWO->getColumn());
        $this->assertSame(2, RouletteNumber::FIVE->getColumn());
        $this->assertSame(2, RouletteNumber::EIGHT->getColumn());
        $this->assertSame(2, RouletteNumber::THIRTY_FIVE->getColumn());
    }

    public function testGetColumnReturnsThirdColumn(): void
    {
        $this->assertSame(3, RouletteNumber::THREE->getColumn());
        $this->assertSame(3, RouletteNumber::SIX->getColumn());
        $this->assertSame(3, RouletteNumber::NINE->getColumn());
        $this->assertSame(3, RouletteNumber::THIRTY_SIX->getColumn());
    }

    public function testGetColumnReturnsNullForZeros(): void
    {
        $this->assertNull(RouletteNumber::ZERO->getColumn());
        $this->assertNull(RouletteNumber::DOUBLE_ZERO->getColumn());
    }

    public function testAllReturnsAllNumbers(): void
    {
        $all = RouletteNumber::all();

        $this->assertCount(38, $all); // 0, 00, 1-36
        $this->assertContains(RouletteNumber::ZERO, $all);
        $this->assertContains(RouletteNumber::DOUBLE_ZERO, $all);
        $this->assertContains(RouletteNumber::THIRTY_SIX, $all);
    }

    public function testEuropeanNumbersExcludesDoubleZero(): void
    {
        $european = RouletteNumber::europeanNumbers();

        $this->assertCount(37, $european); // 0, 1-36
        $this->assertContains(RouletteNumber::ZERO, $european);
        $this->assertNotContains(RouletteNumber::DOUBLE_ZERO, $european);
    }

    public function testAmericanNumbersIncludesAll(): void
    {
        $american = RouletteNumber::americanNumbers();

        $this->assertCount(38, $american); // 0, 00, 1-36
        $this->assertContains(RouletteNumber::ZERO, $american);
        $this->assertContains(RouletteNumber::DOUBLE_ZERO, $american);
    }

    public function testEachNumberHasExactlyOneColor(): void
    {
        foreach (RouletteNumber::all() as $number) {
            $colors = 0;
            if ($number->isRed()) {
                $colors++;
            }
            if ($number->isBlack()) {
                $colors++;
            }
            if ($number->isGreen()) {
                $colors++;
            }

            $this->assertSame(1, $colors, "Number {$number->value} should have exactly one color");
        }
    }
}
