<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Tests\Roulette\Unit\Service;

use Ecourty\PHPCasino\Roulette\Enum\RouletteNumber;
use Ecourty\PHPCasino\Roulette\Enum\RouletteType;
use Ecourty\PHPCasino\Roulette\Model\Bet;
use Ecourty\PHPCasino\Roulette\Service\OddsCalculator;
use PHPUnit\Framework\TestCase;

final class OddsCalculatorTest extends TestCase
{
    private OddsCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new OddsCalculator();
    }

    // ========== EUROPEAN ROULETTE TESTS (37 numbers) ==========

    public function testStraightUpBetEuropean(): void
    {
        $bet = Bet::straightUp(RouletteNumber::SEVEN, 10.0);
        $result = $this->calculator->calculate($bet, RouletteType::EUROPEAN);

        // 1 winning number out of 37
        self::assertSame(1, $result->winningNumbersCount);
        self::assertSame(37, $result->totalNumbers);
        self::assertSame(35, $result->payoutRatio);

        // Win probability: 1/37 = 2.7027%
        self::assertEqualsWithDelta(1.0 / 37.0, $result->winProbability, 0.0001);
        self::assertEqualsWithDelta(2.7027, $result->getWinPercentage(), 0.01);

        // Expected value: (1 × 36 - 37) / 37 × 10 = -0.27
        self::assertEqualsWithDelta(-0.27, $result->expectedValue, 0.01);

        // House edge: 2.7%
        self::assertEqualsWithDelta(0.027, $result->houseEdge, 0.001);
    }

    public function testSplitBetEuropean(): void
    {
        $bet = Bet::split([RouletteNumber::ONE, RouletteNumber::TWO], 10.0);
        $result = $this->calculator->calculate($bet, RouletteType::EUROPEAN);

        // 2 winning numbers out of 37
        self::assertSame(2, $result->winningNumbersCount);
        self::assertSame(37, $result->totalNumbers);
        self::assertSame(17, $result->payoutRatio);

        // Win probability: 2/37 = 5.405%
        self::assertEqualsWithDelta(2.0 / 37.0, $result->winProbability, 0.0001);

        // Expected value should be negative (house edge)
        self::assertLessThan(0.0, $result->expectedValue);

        // House edge: 2.7%
        self::assertEqualsWithDelta(0.027, $result->houseEdge, 0.001);
    }

    public function testStreetBetEuropean(): void
    {
        $bet = Bet::street([RouletteNumber::ONE, RouletteNumber::TWO, RouletteNumber::THREE], 10.0);
        $result = $this->calculator->calculate($bet, RouletteType::EUROPEAN);

        // 3 winning numbers out of 37
        self::assertSame(3, $result->winningNumbersCount);
        self::assertSame(37, $result->totalNumbers);
        self::assertSame(11, $result->payoutRatio);

        // Win probability: 3/37 = 8.108%
        self::assertEqualsWithDelta(3.0 / 37.0, $result->winProbability, 0.0001);

        // House edge: 2.7%
        self::assertEqualsWithDelta(0.027, $result->houseEdge, 0.001);
    }

    public function testCornerBetEuropean(): void
    {
        $bet = Bet::corner([
            RouletteNumber::ONE,
            RouletteNumber::TWO,
            RouletteNumber::FOUR,
            RouletteNumber::FIVE,
        ], 10.0);
        $result = $this->calculator->calculate($bet, RouletteType::EUROPEAN);

        // 4 winning numbers out of 37
        self::assertSame(4, $result->winningNumbersCount);
        self::assertSame(37, $result->totalNumbers);
        self::assertSame(8, $result->payoutRatio);

        // Win probability: 4/37 = 10.811%
        self::assertEqualsWithDelta(4.0 / 37.0, $result->winProbability, 0.0001);

        // House edge: 2.7%
        self::assertEqualsWithDelta(0.027, $result->houseEdge, 0.001);
    }

    public function testLineBetEuropean(): void
    {
        $bet = Bet::line([
            RouletteNumber::ONE,
            RouletteNumber::TWO,
            RouletteNumber::THREE,
            RouletteNumber::FOUR,
            RouletteNumber::FIVE,
            RouletteNumber::SIX,
        ], 10.0);
        $result = $this->calculator->calculate($bet, RouletteType::EUROPEAN);

        // 6 winning numbers out of 37
        self::assertSame(6, $result->winningNumbersCount);
        self::assertSame(37, $result->totalNumbers);
        self::assertSame(5, $result->payoutRatio);

        // Win probability: 6/37 = 16.216%
        self::assertEqualsWithDelta(6.0 / 37.0, $result->winProbability, 0.0001);

        // House edge: 2.7%
        self::assertEqualsWithDelta(0.027, $result->houseEdge, 0.001);
    }

    public function testRedBetEuropean(): void
    {
        $bet = Bet::red(10.0);
        $result = $this->calculator->calculate($bet, RouletteType::EUROPEAN);

        // 18 red numbers out of 37
        self::assertSame(18, $result->winningNumbersCount);
        self::assertSame(37, $result->totalNumbers);
        self::assertSame(1, $result->payoutRatio);

        // Win probability: 18/37 = 48.649%
        self::assertEqualsWithDelta(18.0 / 37.0, $result->winProbability, 0.0001);
        self::assertEqualsWithDelta(48.649, $result->getWinPercentage(), 0.01);

        // Expected value: (18 × 2 - 37) / 37 × 10 = -0.27
        self::assertEqualsWithDelta(-0.27, $result->expectedValue, 0.01);

        // House edge: 2.7%
        self::assertEqualsWithDelta(0.027, $result->houseEdge, 0.001);
    }

    public function testBlackBetEuropean(): void
    {
        $bet = Bet::black(10.0);
        $result = $this->calculator->calculate($bet, RouletteType::EUROPEAN);

        // 18 black numbers out of 37
        self::assertSame(18, $result->winningNumbersCount);
        self::assertSame(37, $result->totalNumbers);

        // Same as red
        self::assertEqualsWithDelta(18.0 / 37.0, $result->winProbability, 0.0001);
        self::assertEqualsWithDelta(-0.27, $result->expectedValue, 0.01);
        self::assertEqualsWithDelta(0.027, $result->houseEdge, 0.001);
    }

    public function testEvenBetEuropean(): void
    {
        $bet = Bet::even(10.0);
        $result = $this->calculator->calculate($bet, RouletteType::EUROPEAN);

        // 18 even numbers out of 37 (excluding 0)
        self::assertSame(18, $result->winningNumbersCount);
        self::assertSame(37, $result->totalNumbers);

        self::assertEqualsWithDelta(18.0 / 37.0, $result->winProbability, 0.0001);
        self::assertEqualsWithDelta(-0.27, $result->expectedValue, 0.01);
        self::assertEqualsWithDelta(0.027, $result->houseEdge, 0.001);
    }

    public function testOddBetEuropean(): void
    {
        $bet = Bet::odd(10.0);
        $result = $this->calculator->calculate($bet, RouletteType::EUROPEAN);

        // 18 odd numbers out of 37
        self::assertSame(18, $result->winningNumbersCount);
        self::assertSame(37, $result->totalNumbers);

        self::assertEqualsWithDelta(18.0 / 37.0, $result->winProbability, 0.0001);
        self::assertEqualsWithDelta(-0.27, $result->expectedValue, 0.01);
        self::assertEqualsWithDelta(0.027, $result->houseEdge, 0.001);
    }

    public function testLowBetEuropean(): void
    {
        $bet = Bet::low(10.0);
        $result = $this->calculator->calculate($bet, RouletteType::EUROPEAN);

        // 18 low numbers (1-18) out of 37
        self::assertSame(18, $result->winningNumbersCount);
        self::assertSame(37, $result->totalNumbers);

        self::assertEqualsWithDelta(18.0 / 37.0, $result->winProbability, 0.0001);
        self::assertEqualsWithDelta(-0.27, $result->expectedValue, 0.01);
        self::assertEqualsWithDelta(0.027, $result->houseEdge, 0.001);
    }

    public function testHighBetEuropean(): void
    {
        $bet = Bet::high(10.0);
        $result = $this->calculator->calculate($bet, RouletteType::EUROPEAN);

        // 18 high numbers (19-36) out of 37
        self::assertSame(18, $result->winningNumbersCount);
        self::assertSame(37, $result->totalNumbers);

        self::assertEqualsWithDelta(18.0 / 37.0, $result->winProbability, 0.0001);
        self::assertEqualsWithDelta(-0.27, $result->expectedValue, 0.01);
        self::assertEqualsWithDelta(0.027, $result->houseEdge, 0.001);
    }

    public function testDozenBetEuropean(): void
    {
        $bet = Bet::dozen(1, 10.0);
        $result = $this->calculator->calculate($bet, RouletteType::EUROPEAN);

        // 12 numbers in a dozen out of 37
        self::assertSame(12, $result->winningNumbersCount);
        self::assertSame(37, $result->totalNumbers);
        self::assertSame(2, $result->payoutRatio);

        // Win probability: 12/37 = 32.432%
        self::assertEqualsWithDelta(12.0 / 37.0, $result->winProbability, 0.0001);

        // House edge: 2.7%
        self::assertEqualsWithDelta(0.027, $result->houseEdge, 0.001);
    }

    public function testColumnBetEuropean(): void
    {
        $bet = Bet::column(1, 10.0);
        $result = $this->calculator->calculate($bet, RouletteType::EUROPEAN);

        // 12 numbers in a column out of 37
        self::assertSame(12, $result->winningNumbersCount);
        self::assertSame(37, $result->totalNumbers);
        self::assertSame(2, $result->payoutRatio);

        // Win probability: 12/37 = 32.432%
        self::assertEqualsWithDelta(12.0 / 37.0, $result->winProbability, 0.0001);

        // House edge: 2.7%
        self::assertEqualsWithDelta(0.027, $result->houseEdge, 0.001);
    }

    // ========== AMERICAN ROULETTE TESTS (38 numbers) ==========

    public function testStraightUpBetAmerican(): void
    {
        $bet = Bet::straightUp(RouletteNumber::SEVEN, 10.0);
        $result = $this->calculator->calculate($bet, RouletteType::AMERICAN);

        // 1 winning number out of 38
        self::assertSame(1, $result->winningNumbersCount);
        self::assertSame(38, $result->totalNumbers);
        self::assertSame(35, $result->payoutRatio);

        // Win probability: 1/38 = 2.632%
        self::assertEqualsWithDelta(1.0 / 38.0, $result->winProbability, 0.0001);
        self::assertEqualsWithDelta(2.632, $result->getWinPercentage(), 0.01);

        // Expected value: (1 × 36 - 38) / 38 × 10 = -0.526
        self::assertEqualsWithDelta(-0.526, $result->expectedValue, 0.01);

        // House edge: 5.26%
        self::assertEqualsWithDelta(0.0526, $result->houseEdge, 0.001);
    }

    public function testRedBetAmerican(): void
    {
        $bet = Bet::red(10.0);
        $result = $this->calculator->calculate($bet, RouletteType::AMERICAN);

        // 18 red numbers out of 38
        self::assertSame(18, $result->winningNumbersCount);
        self::assertSame(38, $result->totalNumbers);
        self::assertSame(1, $result->payoutRatio);

        // Win probability: 18/38 = 47.368%
        self::assertEqualsWithDelta(18.0 / 38.0, $result->winProbability, 0.0001);
        self::assertEqualsWithDelta(47.368, $result->getWinPercentage(), 0.01);

        // Expected value: (18 × 2 - 38) / 38 × 10 = -0.526
        self::assertEqualsWithDelta(-0.526, $result->expectedValue, 0.01);

        // House edge: 5.26%
        self::assertEqualsWithDelta(0.0526, $result->houseEdge, 0.001);
    }

    public function testFiveNumberBetAmerican(): void
    {
        $bet = Bet::fiveNumber(10.0);
        $result = $this->calculator->calculate($bet, RouletteType::AMERICAN);

        // 5 winning numbers out of 38 (0, 00, 1, 2, 3)
        self::assertSame(5, $result->winningNumbersCount);
        self::assertSame(38, $result->totalNumbers);
        self::assertSame(6, $result->payoutRatio);

        // Win probability: 5/38 = 13.158%
        self::assertEqualsWithDelta(5.0 / 38.0, $result->winProbability, 0.0001);
        self::assertEqualsWithDelta(13.158, $result->getWinPercentage(), 0.01);

        // Expected value: (5 × 7 - 38) / 38 × 10 = -0.789
        // This is the WORST bet with 7.89% house edge!
        self::assertEqualsWithDelta(-0.789, $result->expectedValue, 0.01);

        // House edge: 7.89% (worse than standard 5.26%)
        self::assertEqualsWithDelta(0.0789, $result->houseEdge, 0.001);
        self::assertGreaterThan(0.0526, $result->houseEdge); // Worse than other American bets
    }

    public function testDozenBetAmerican(): void
    {
        $bet = Bet::dozen(2, 10.0);
        $result = $this->calculator->calculate($bet, RouletteType::AMERICAN);

        // 12 numbers in a dozen out of 38
        self::assertSame(12, $result->winningNumbersCount);
        self::assertSame(38, $result->totalNumbers);
        self::assertSame(2, $result->payoutRatio);

        // Win probability: 12/38 = 31.579%
        self::assertEqualsWithDelta(12.0 / 38.0, $result->winProbability, 0.0001);

        // House edge: 5.26%
        self::assertEqualsWithDelta(0.0526, $result->houseEdge, 0.001);
    }

    // ========== ADDITIONAL TESTS ==========

    public function testBetAmountAffectsExpectedValue(): void
    {
        $bet1 = Bet::red(10.0);
        $bet2 = Bet::red(100.0);

        $result1 = $this->calculator->calculate($bet1, RouletteType::EUROPEAN);
        $result2 = $this->calculator->calculate($bet2, RouletteType::EUROPEAN);

        // Expected value should scale with bet amount
        self::assertEqualsWithDelta(-0.27, $result1->expectedValue, 0.01);
        self::assertEqualsWithDelta(-2.70, $result2->expectedValue, 0.01);

        // But probabilities and house edge remain the same
        self::assertEqualsWithDelta($result1->winProbability, $result2->winProbability, 0.0001);
        self::assertEqualsWithDelta($result1->houseEdge, $result2->houseEdge, 0.001);
    }

    public function testAllEuropeanOutsideBetsHaveSameHouseEdge(): void
    {
        $bets = [
            Bet::red(10.0),
            Bet::black(10.0),
            Bet::even(10.0),
            Bet::odd(10.0),
            Bet::low(10.0),
            Bet::high(10.0),
            Bet::dozen(1, 10.0),
            Bet::column(2, 10.0),
        ];

        foreach ($bets as $bet) {
            $result = $this->calculator->calculate($bet, RouletteType::EUROPEAN);
            self::assertEqualsWithDelta(0.027, $result->houseEdge, 0.001, "Failed for bet type: {$bet->getType()->value}");
        }
    }

    public function testAllAmericanInsideBetsHaveSameHouseEdge(): void
    {
        $bets = [
            Bet::straightUp(RouletteNumber::SEVEN, 10.0),
            Bet::split([RouletteNumber::ONE, RouletteNumber::TWO], 10.0),
            Bet::street([RouletteNumber::ONE, RouletteNumber::TWO, RouletteNumber::THREE], 10.0),
            Bet::corner([RouletteNumber::ONE, RouletteNumber::TWO, RouletteNumber::FOUR, RouletteNumber::FIVE], 10.0),
        ];

        foreach ($bets as $bet) {
            $result = $this->calculator->calculate($bet, RouletteType::AMERICAN);
            self::assertEqualsWithDelta(0.0526, $result->houseEdge, 0.001, "Failed for bet type: {$bet->getType()->value}");
        }
    }

    public function testWinPayoutAndProfitCalculations(): void
    {
        $bet = Bet::straightUp(RouletteNumber::SEVEN, 10.0);
        $result = $this->calculator->calculate($bet, RouletteType::EUROPEAN);

        // Win payout: 10 × (35 + 1) = 360
        self::assertEqualsWithDelta(360.0, $result->getWinPayout(), 0.01);

        // Win profit: 10 × 35 = 350
        self::assertEqualsWithDelta(350.0, $result->getWinProfit(), 0.01);
    }
}
