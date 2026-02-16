<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Tests\Roulette\Unit\Model;

use Ecourty\PHPCasino\Roulette\Enum\BetType;
use Ecourty\PHPCasino\Roulette\Enum\RouletteNumber;
use Ecourty\PHPCasino\Roulette\Exception\InvalidBetException;
use Ecourty\PHPCasino\Roulette\Model\Bet;
use PHPUnit\Framework\TestCase;

class BetTest extends TestCase
{
    // ===== Constructor & Validation Tests =====

    public function testConstructorThrowsOnNegativeAmount(): void
    {
        $this->expectException(InvalidBetException::class);
        $this->expectExceptionMessage('must be positive');

        new Bet(BetType::RED, -10.0);
    }

    public function testConstructorThrowsOnZeroAmount(): void
    {
        $this->expectException(InvalidBetException::class);

        new Bet(BetType::RED, 0.0);
    }

    public function testConstructorThrowsOnInvalidNumberCountForStraightUp(): void
    {
        $this->expectException(InvalidBetException::class);
        $this->expectExceptionMessage('expected 1, got 2');

        new Bet(BetType::STRAIGHT_UP, 10.0, [RouletteNumber::ONE, RouletteNumber::TWO]);
    }

    public function testConstructorThrowsOnMissingPositionForDozen(): void
    {
        $this->expectException(InvalidBetException::class);
        $this->expectExceptionMessage('Position required');

        new Bet(BetType::DOZEN, 10.0);
    }

    public function testConstructorThrowsOnInvalidDozenPosition(): void
    {
        $this->expectException(InvalidBetException::class);
        $this->expectExceptionMessage('Dozen must be 1, 2, or 3');

        new Bet(BetType::DOZEN, 10.0, [], 4);
    }

    public function testConstructorThrowsOnInvalidColumnPosition(): void
    {
        $this->expectException(InvalidBetException::class);
        $this->expectExceptionMessage('Column must be 1, 2, or 3');

        new Bet(BetType::COLUMN, 10.0, [], 0);
    }

    // ===== Factory Method Tests =====

    public function testStraightUpFactory(): void
    {
        $bet = Bet::straightUp(RouletteNumber::SEVEN, 10.0);

        $this->assertSame(BetType::STRAIGHT_UP, $bet->getType());
        $this->assertSame(10.0, $bet->getAmount());
        $this->assertCount(1, $bet->getNumbers());
    }

    public function testRedFactory(): void
    {
        $bet = Bet::red(25.5);

        $this->assertSame(BetType::RED, $bet->getType());
        $this->assertSame(25.5, $bet->getAmount());
    }

    public function testDozenFactory(): void
    {
        $bet = Bet::dozen(2, 50.0);

        $this->assertSame(BetType::DOZEN, $bet->getType());
        $this->assertSame(2, $bet->getPosition());
    }

    // ===== Straight Up Bet Tests =====

    public function testStraightUpIsWinningWhenNumberMatches(): void
    {
        $bet = Bet::straightUp(RouletteNumber::SEVEN, 10.0);

        $this->assertTrue($bet->isWinning(RouletteNumber::SEVEN));
    }

    public function testStraightUpIsNotWinningWhenNumberDoesNotMatch(): void
    {
        $bet = Bet::straightUp(RouletteNumber::SEVEN, 10.0);

        $this->assertFalse($bet->isWinning(RouletteNumber::EIGHT));
        $this->assertFalse($bet->isWinning(RouletteNumber::ZERO));
    }

    public function testStraightUpPayoutCalculation(): void
    {
        $bet = Bet::straightUp(RouletteNumber::SEVEN, 10.0);

        // Win: 10 * (35 + 1) = 360
        $this->assertSame(360.0, $bet->calculatePayout(RouletteNumber::SEVEN));
        // Loss: 0
        $this->assertSame(0.0, $bet->calculatePayout(RouletteNumber::EIGHT));
    }

    public function testStraightUpProfitCalculation(): void
    {
        $bet = Bet::straightUp(RouletteNumber::SEVEN, 10.0);

        // Win: 10 * 35 = 350 profit
        $this->assertSame(350.0, $bet->calculateProfit(RouletteNumber::SEVEN));
        // Loss: -10
        $this->assertSame(-10.0, $bet->calculateProfit(RouletteNumber::EIGHT));
    }

    public function testStraightUpOnZero(): void
    {
        $bet = Bet::straightUp(RouletteNumber::ZERO, 5.0);

        $this->assertTrue($bet->isWinning(RouletteNumber::ZERO));
        $this->assertFalse($bet->isWinning(RouletteNumber::DOUBLE_ZERO));
        $this->assertSame(180.0, $bet->calculatePayout(RouletteNumber::ZERO)); // 5 * 36
    }

    public function testStraightUpOnDoubleZero(): void
    {
        $bet = Bet::straightUp(RouletteNumber::DOUBLE_ZERO, 5.0);

        $this->assertTrue($bet->isWinning(RouletteNumber::DOUBLE_ZERO));
        $this->assertFalse($bet->isWinning(RouletteNumber::ZERO));
    }

    // ===== Red/Black Bet Tests =====

    public function testRedBetIsWinningOnRedNumbers(): void
    {
        $bet = Bet::red(10.0);

        $this->assertTrue($bet->isWinning(RouletteNumber::ONE));
        $this->assertTrue($bet->isWinning(RouletteNumber::THREE));
        $this->assertTrue($bet->isWinning(RouletteNumber::SEVEN));
        $this->assertTrue($bet->isWinning(RouletteNumber::THIRTY_SIX));
    }

    public function testRedBetIsNotWinningOnBlackOrGreen(): void
    {
        $bet = Bet::red(10.0);

        $this->assertFalse($bet->isWinning(RouletteNumber::TWO));
        $this->assertFalse($bet->isWinning(RouletteNumber::ZERO));
        $this->assertFalse($bet->isWinning(RouletteNumber::DOUBLE_ZERO));
    }

    public function testBlackBetIsWinningOnBlackNumbers(): void
    {
        $bet = Bet::black(10.0);

        $this->assertTrue($bet->isWinning(RouletteNumber::TWO));
        $this->assertTrue($bet->isWinning(RouletteNumber::FOUR));
        $this->assertTrue($bet->isWinning(RouletteNumber::THIRTY_FIVE));
    }

    public function testBlackBetIsNotWinningOnRedOrGreen(): void
    {
        $bet = Bet::black(10.0);

        $this->assertFalse($bet->isWinning(RouletteNumber::ONE));
        $this->assertFalse($bet->isWinning(RouletteNumber::ZERO));
    }

    public function testRedBlackPayout(): void
    {
        $betRed = Bet::red(20.0);
        $betBlack = Bet::black(20.0);

        // Win: 20 * (1 + 1) = 40
        $this->assertSame(40.0, $betRed->calculatePayout(RouletteNumber::ONE));
        $this->assertSame(40.0, $betBlack->calculatePayout(RouletteNumber::TWO));

        // Loss: 0
        $this->assertSame(0.0, $betRed->calculatePayout(RouletteNumber::TWO));
        $this->assertSame(0.0, $betBlack->calculatePayout(RouletteNumber::ONE));
    }

    // ===== Even/Odd Bet Tests =====

    public function testEvenBetIsWinningOnEvenNumbers(): void
    {
        $bet = Bet::even(10.0);

        $this->assertTrue($bet->isWinning(RouletteNumber::TWO));
        $this->assertTrue($bet->isWinning(RouletteNumber::FOUR));
        $this->assertTrue($bet->isWinning(RouletteNumber::EIGHTEEN));
        $this->assertTrue($bet->isWinning(RouletteNumber::THIRTY_SIX));
    }

    public function testEvenBetIsNotWinningOnOddOrZero(): void
    {
        $bet = Bet::even(10.0);

        $this->assertFalse($bet->isWinning(RouletteNumber::ONE));
        $this->assertFalse($bet->isWinning(RouletteNumber::THREE));
        $this->assertFalse($bet->isWinning(RouletteNumber::ZERO));
        $this->assertFalse($bet->isWinning(RouletteNumber::DOUBLE_ZERO));
    }

    public function testOddBetIsWinningOnOddNumbers(): void
    {
        $bet = Bet::odd(10.0);

        $this->assertTrue($bet->isWinning(RouletteNumber::ONE));
        $this->assertTrue($bet->isWinning(RouletteNumber::THREE));
        $this->assertTrue($bet->isWinning(RouletteNumber::THIRTY_FIVE));
    }

    public function testOddBetIsNotWinningOnEvenOrZero(): void
    {
        $bet = Bet::odd(10.0);

        $this->assertFalse($bet->isWinning(RouletteNumber::TWO));
        $this->assertFalse($bet->isWinning(RouletteNumber::ZERO));
    }

    // ===== Low/High Bet Tests =====

    public function testLowBetIsWinningOnLowNumbers(): void
    {
        $bet = Bet::low(10.0);

        $this->assertTrue($bet->isWinning(RouletteNumber::ONE));
        $this->assertTrue($bet->isWinning(RouletteNumber::NINE));
        $this->assertTrue($bet->isWinning(RouletteNumber::EIGHTEEN));
    }

    public function testLowBetIsNotWinningOnHighOrZero(): void
    {
        $bet = Bet::low(10.0);

        $this->assertFalse($bet->isWinning(RouletteNumber::NINETEEN));
        $this->assertFalse($bet->isWinning(RouletteNumber::THIRTY_SIX));
        $this->assertFalse($bet->isWinning(RouletteNumber::ZERO));
    }

    public function testHighBetIsWinningOnHighNumbers(): void
    {
        $bet = Bet::high(10.0);

        $this->assertTrue($bet->isWinning(RouletteNumber::NINETEEN));
        $this->assertTrue($bet->isWinning(RouletteNumber::TWENTY_FIVE));
        $this->assertTrue($bet->isWinning(RouletteNumber::THIRTY_SIX));
    }

    public function testHighBetIsNotWinningOnLowOrZero(): void
    {
        $bet = Bet::high(10.0);

        $this->assertFalse($bet->isWinning(RouletteNumber::ONE));
        $this->assertFalse($bet->isWinning(RouletteNumber::EIGHTEEN));
        $this->assertFalse($bet->isWinning(RouletteNumber::ZERO));
    }

    // ===== Dozen Bet Tests =====

    public function testDozenBetFirstDozen(): void
    {
        $bet = Bet::dozen(1, 10.0);

        $this->assertTrue($bet->isWinning(RouletteNumber::ONE));
        $this->assertTrue($bet->isWinning(RouletteNumber::SIX));
        $this->assertTrue($bet->isWinning(RouletteNumber::TWELVE));
        $this->assertFalse($bet->isWinning(RouletteNumber::THIRTEEN));
        $this->assertFalse($bet->isWinning(RouletteNumber::ZERO));
    }

    public function testDozenBetSecondDozen(): void
    {
        $bet = Bet::dozen(2, 10.0);

        $this->assertTrue($bet->isWinning(RouletteNumber::THIRTEEN));
        $this->assertTrue($bet->isWinning(RouletteNumber::EIGHTEEN));
        $this->assertTrue($bet->isWinning(RouletteNumber::TWENTY_FOUR));
        $this->assertFalse($bet->isWinning(RouletteNumber::TWELVE));
        $this->assertFalse($bet->isWinning(RouletteNumber::TWENTY_FIVE));
    }

    public function testDozenBetThirdDozen(): void
    {
        $bet = Bet::dozen(3, 10.0);

        $this->assertTrue($bet->isWinning(RouletteNumber::TWENTY_FIVE));
        $this->assertTrue($bet->isWinning(RouletteNumber::THIRTY));
        $this->assertTrue($bet->isWinning(RouletteNumber::THIRTY_SIX));
        $this->assertFalse($bet->isWinning(RouletteNumber::TWENTY_FOUR));
    }

    public function testDozenBetPayout(): void
    {
        $bet = Bet::dozen(1, 30.0);

        // Win: 30 * (2 + 1) = 90
        $this->assertSame(90.0, $bet->calculatePayout(RouletteNumber::ONE));
        // Loss: 0
        $this->assertSame(0.0, $bet->calculatePayout(RouletteNumber::THIRTEEN));
    }

    // ===== Column Bet Tests =====

    public function testColumnBetFirstColumn(): void
    {
        $bet = Bet::column(1, 10.0);

        $this->assertTrue($bet->isWinning(RouletteNumber::ONE));
        $this->assertTrue($bet->isWinning(RouletteNumber::FOUR));
        $this->assertTrue($bet->isWinning(RouletteNumber::SEVEN));
        $this->assertTrue($bet->isWinning(RouletteNumber::THIRTY_FOUR));
        $this->assertFalse($bet->isWinning(RouletteNumber::TWO));
        $this->assertFalse($bet->isWinning(RouletteNumber::THREE));
    }

    public function testColumnBetSecondColumn(): void
    {
        $bet = Bet::column(2, 10.0);

        $this->assertTrue($bet->isWinning(RouletteNumber::TWO));
        $this->assertTrue($bet->isWinning(RouletteNumber::FIVE));
        $this->assertTrue($bet->isWinning(RouletteNumber::THIRTY_FIVE));
        $this->assertFalse($bet->isWinning(RouletteNumber::ONE));
    }

    public function testColumnBetThirdColumn(): void
    {
        $bet = Bet::column(3, 10.0);

        $this->assertTrue($bet->isWinning(RouletteNumber::THREE));
        $this->assertTrue($bet->isWinning(RouletteNumber::SIX));
        $this->assertTrue($bet->isWinning(RouletteNumber::THIRTY_SIX));
        $this->assertFalse($bet->isWinning(RouletteNumber::ONE));
    }

    public function testColumnBetPayout(): void
    {
        $bet = Bet::column(1, 15.0);

        // Win: 15 * (2 + 1) = 45
        $this->assertSame(45.0, $bet->calculatePayout(RouletteNumber::ONE));
        // Loss: 0
        $this->assertSame(0.0, $bet->calculatePayout(RouletteNumber::TWO));
    }

    // ===== Split Bet Tests =====

    public function testSplitBetIsWinningOnEitherNumber(): void
    {
        $bet = Bet::split([RouletteNumber::ONE, RouletteNumber::TWO], 10.0);

        $this->assertTrue($bet->isWinning(RouletteNumber::ONE));
        $this->assertTrue($bet->isWinning(RouletteNumber::TWO));
        $this->assertFalse($bet->isWinning(RouletteNumber::THREE));
    }

    public function testSplitBetPayout(): void
    {
        $bet = Bet::split([RouletteNumber::ONE, RouletteNumber::TWO], 10.0);

        // Win: 10 * (17 + 1) = 180
        $this->assertSame(180.0, $bet->calculatePayout(RouletteNumber::ONE));
        $this->assertSame(180.0, $bet->calculatePayout(RouletteNumber::TWO));
        // Loss: 0
        $this->assertSame(0.0, $bet->calculatePayout(RouletteNumber::THREE));
    }

    // ===== Street Bet Tests =====

    public function testStreetBetIsWinningOnAnyOfThreeNumbers(): void
    {
        $bet = Bet::street([RouletteNumber::ONE, RouletteNumber::TWO, RouletteNumber::THREE], 10.0);

        $this->assertTrue($bet->isWinning(RouletteNumber::ONE));
        $this->assertTrue($bet->isWinning(RouletteNumber::TWO));
        $this->assertTrue($bet->isWinning(RouletteNumber::THREE));
        $this->assertFalse($bet->isWinning(RouletteNumber::FOUR));
    }

    public function testStreetBetPayout(): void
    {
        $bet = Bet::street([RouletteNumber::ONE, RouletteNumber::TWO, RouletteNumber::THREE], 10.0);

        // Win: 10 * (11 + 1) = 120
        $this->assertSame(120.0, $bet->calculatePayout(RouletteNumber::TWO));
        // Loss: 0
        $this->assertSame(0.0, $bet->calculatePayout(RouletteNumber::FOUR));
    }

    // ===== Corner Bet Tests =====

    public function testCornerBetIsWinningOnAnyOfFourNumbers(): void
    {
        $bet = Bet::corner([
            RouletteNumber::ONE,
            RouletteNumber::TWO,
            RouletteNumber::FOUR,
            RouletteNumber::FIVE,
        ], 10.0);

        $this->assertTrue($bet->isWinning(RouletteNumber::ONE));
        $this->assertTrue($bet->isWinning(RouletteNumber::TWO));
        $this->assertTrue($bet->isWinning(RouletteNumber::FOUR));
        $this->assertTrue($bet->isWinning(RouletteNumber::FIVE));
        $this->assertFalse($bet->isWinning(RouletteNumber::THREE));
    }

    public function testCornerBetPayout(): void
    {
        $bet = Bet::corner([
            RouletteNumber::ONE,
            RouletteNumber::TWO,
            RouletteNumber::FOUR,
            RouletteNumber::FIVE,
        ], 10.0);

        // Win: 10 * (8 + 1) = 90
        $this->assertSame(90.0, $bet->calculatePayout(RouletteNumber::FIVE));
        // Loss: 0
        $this->assertSame(0.0, $bet->calculatePayout(RouletteNumber::SIX));
    }

    // ===== Five Number Bet Tests =====

    public function testFiveNumberBetIsWinningOnZeroDoubleZeroOneTwoThree(): void
    {
        $bet = Bet::fiveNumber(10.0);

        $this->assertTrue($bet->isWinning(RouletteNumber::ZERO));
        $this->assertTrue($bet->isWinning(RouletteNumber::DOUBLE_ZERO));
        $this->assertTrue($bet->isWinning(RouletteNumber::ONE));
        $this->assertTrue($bet->isWinning(RouletteNumber::TWO));
        $this->assertTrue($bet->isWinning(RouletteNumber::THREE));
        $this->assertFalse($bet->isWinning(RouletteNumber::FOUR));
    }

    public function testFiveNumberBetPayout(): void
    {
        $bet = Bet::fiveNumber(10.0);

        // Win: 10 * (6 + 1) = 70
        $this->assertSame(70.0, $bet->calculatePayout(RouletteNumber::ZERO));
        $this->assertSame(70.0, $bet->calculatePayout(RouletteNumber::DOUBLE_ZERO));
        // Loss: 0
        $this->assertSame(0.0, $bet->calculatePayout(RouletteNumber::FOUR));
    }

    // ===== Line Bet Tests =====

    public function testLineBetIsWinningOnAnyOfSixNumbers(): void
    {
        $bet = Bet::line([
            RouletteNumber::ONE,
            RouletteNumber::TWO,
            RouletteNumber::THREE,
            RouletteNumber::FOUR,
            RouletteNumber::FIVE,
            RouletteNumber::SIX,
        ], 10.0);

        $this->assertTrue($bet->isWinning(RouletteNumber::ONE));
        $this->assertTrue($bet->isWinning(RouletteNumber::SIX));
        $this->assertFalse($bet->isWinning(RouletteNumber::SEVEN));
    }

    public function testLineBetPayout(): void
    {
        $bet = Bet::line([
            RouletteNumber::ONE,
            RouletteNumber::TWO,
            RouletteNumber::THREE,
            RouletteNumber::FOUR,
            RouletteNumber::FIVE,
            RouletteNumber::SIX,
        ], 10.0);

        // Win: 10 * (5 + 1) = 60
        $this->assertSame(60.0, $bet->calculatePayout(RouletteNumber::THREE));
        // Loss: 0
        $this->assertSame(0.0, $bet->calculatePayout(RouletteNumber::SEVEN));
    }

    // ===== Profit Calculation Tests =====

    public function testProfitCalculationForWinningBets(): void
    {
        $straightUp = Bet::straightUp(RouletteNumber::SEVEN, 10.0);
        $red = Bet::red(10.0);
        $dozen = Bet::dozen(1, 10.0);

        // Straight up: 10 * 35 = 350 profit
        $this->assertSame(350.0, $straightUp->calculateProfit(RouletteNumber::SEVEN));
        // Red: 10 * 1 = 10 profit
        $this->assertSame(10.0, $red->calculateProfit(RouletteNumber::ONE));
        // Dozen: 10 * 2 = 20 profit
        $this->assertSame(20.0, $dozen->calculateProfit(RouletteNumber::SIX));
    }

    public function testProfitCalculationForLosingBets(): void
    {
        $straightUp = Bet::straightUp(RouletteNumber::SEVEN, 10.0);
        $red = Bet::red(10.0);

        // All losses return negative amount (stake lost)
        $this->assertSame(-10.0, $straightUp->calculateProfit(RouletteNumber::EIGHT));
        $this->assertSame(-10.0, $red->calculateProfit(RouletteNumber::TWO));
    }

    // ===== Edge Cases Tests =====

    public function testAllBetsLoseOnZero(): void
    {
        $red = Bet::red(10.0);
        $black = Bet::black(10.0);
        $even = Bet::even(10.0);
        $odd = Bet::odd(10.0);
        $low = Bet::low(10.0);
        $high = Bet::high(10.0);
        $dozen = Bet::dozen(1, 10.0);
        $column = Bet::column(1, 10.0);

        // All outside bets lose on zero
        $this->assertFalse($red->isWinning(RouletteNumber::ZERO));
        $this->assertFalse($black->isWinning(RouletteNumber::ZERO));
        $this->assertFalse($even->isWinning(RouletteNumber::ZERO));
        $this->assertFalse($odd->isWinning(RouletteNumber::ZERO));
        $this->assertFalse($low->isWinning(RouletteNumber::ZERO));
        $this->assertFalse($high->isWinning(RouletteNumber::ZERO));
        $this->assertFalse($dozen->isWinning(RouletteNumber::ZERO));
        $this->assertFalse($column->isWinning(RouletteNumber::ZERO));
    }

    public function testFractionalBetAmounts(): void
    {
        $bet = Bet::red(12.50);

        $this->assertSame(12.50, $bet->getAmount());
        // Win: 12.50 * 2 = 25.0
        $this->assertSame(25.0, $bet->calculatePayout(RouletteNumber::ONE));
    }

    public function testLargeBetAmounts(): void
    {
        $bet = Bet::straightUp(RouletteNumber::SEVEN, 1000.0);

        // Win: 1000 * 36 = 36000
        $this->assertSame(36000.0, $bet->calculatePayout(RouletteNumber::SEVEN));
    }

    public function testBoundaryNumbers(): void
    {
        // Test boundaries between dozens and columns
        $dozen1 = Bet::dozen(1, 10.0);
        $dozen2 = Bet::dozen(2, 10.0);

        $this->assertTrue($dozen1->isWinning(RouletteNumber::TWELVE));
        $this->assertFalse($dozen1->isWinning(RouletteNumber::THIRTEEN));
        $this->assertTrue($dozen2->isWinning(RouletteNumber::THIRTEEN));

        // Test boundaries between low/high
        $low = Bet::low(10.0);
        $high = Bet::high(10.0);

        $this->assertTrue($low->isWinning(RouletteNumber::EIGHTEEN));
        $this->assertFalse($low->isWinning(RouletteNumber::NINETEEN));
        $this->assertTrue($high->isWinning(RouletteNumber::NINETEEN));
    }

    // ===== BET VALIDATION TESTS =====

    public function testSplitRejectsInvalidCombination(): void
    {
        $this->expectException(InvalidBetException::class);
        $this->expectExceptionMessage('Invalid split bet');

        Bet::split([RouletteNumber::ONE, RouletteNumber::THIRTY_SIX], 10.0);
    }

    public function testStreetRejectsInvalidCombination(): void
    {
        $this->expectException(InvalidBetException::class);
        $this->expectExceptionMessage('Invalid street bet');

        Bet::street([RouletteNumber::SEVEN, RouletteNumber::FOURTEEN, RouletteNumber::TWENTY_SIX], 10.0);
    }

    public function testCornerRejectsInvalidCombination(): void
    {
        $this->expectException(InvalidBetException::class);
        $this->expectExceptionMessage('Invalid corner bet');

        Bet::corner([RouletteNumber::ONE, RouletteNumber::FIVE, RouletteNumber::TEN, RouletteNumber::FIFTEEN], 10.0);
    }

    public function testLineRejectsInvalidCombination(): void
    {
        $this->expectException(InvalidBetException::class);
        $this->expectExceptionMessage('Invalid line bet');

        Bet::line([
            RouletteNumber::ONE,
            RouletteNumber::TWO,
            RouletteNumber::SEVEN,
            RouletteNumber::EIGHT,
            RouletteNumber::NINE,
            RouletteNumber::TEN,
        ], 10.0);
    }

    public function testSplitAcceptsValidCombination(): void
    {
        $bet = Bet::split([RouletteNumber::ONE, RouletteNumber::TWO], 10.0);

        $this->assertInstanceOf(Bet::class, $bet);
    }

    public function testStreetAcceptsValidCombination(): void
    {
        $bet = Bet::street([RouletteNumber::SEVEN, RouletteNumber::EIGHT, RouletteNumber::NINE], 10.0);

        $this->assertInstanceOf(Bet::class, $bet);
    }

    public function testCornerAcceptsValidCombination(): void
    {
        $bet = Bet::corner([
            RouletteNumber::ONE,
            RouletteNumber::TWO,
            RouletteNumber::FOUR,
            RouletteNumber::FIVE,
        ], 10.0);

        $this->assertInstanceOf(Bet::class, $bet);
    }

    public function testLineAcceptsValidCombination(): void
    {
        $bet = Bet::line([
            RouletteNumber::ONE,
            RouletteNumber::TWO,
            RouletteNumber::THREE,
            RouletteNumber::FOUR,
            RouletteNumber::FIVE,
            RouletteNumber::SIX,
        ], 10.0);

        $this->assertInstanceOf(Bet::class, $bet);
    }
}
