<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Tests\Roulette\Unit\Model;

use Ecourty\PHPCasino\Roulette\Model\OddsResult;
use PHPUnit\Framework\TestCase;

final class OddsResultTest extends TestCase
{
    public function testConstructorSetsAllProperties(): void
    {
        $result = new OddsResult(
            winProbability: 0.4865,
            expectedValue: -0.27,
            houseEdge: 0.027,
            betAmount: 10.0,
            winningNumbersCount: 18,
            totalNumbers: 37,
            payoutRatio: 1,
        );

        self::assertSame(0.4865, $result->winProbability);
        self::assertSame(-0.27, $result->expectedValue);
        self::assertSame(0.027, $result->houseEdge);
        self::assertSame(10.0, $result->betAmount);
        self::assertSame(18, $result->winningNumbersCount);
        self::assertSame(37, $result->totalNumbers);
        self::assertSame(1, $result->payoutRatio);
    }

    public function testGetLossProbabilityCalculatesCorrectly(): void
    {
        $result = new OddsResult(
            winProbability: 0.4865,
            expectedValue: 0.0,
            houseEdge: 0.0,
            betAmount: 10.0,
            winningNumbersCount: 18,
            totalNumbers: 37,
            payoutRatio: 1,
        );

        self::assertEqualsWithDelta(0.5135, $result->getLossProbability(), 0.0001);
    }

    public function testGetWinPercentageReturnsCorrectValue(): void
    {
        $result = new OddsResult(
            winProbability: 0.4865,
            expectedValue: 0.0,
            houseEdge: 0.0,
            betAmount: 10.0,
            winningNumbersCount: 18,
            totalNumbers: 37,
            payoutRatio: 1,
        );

        self::assertEqualsWithDelta(48.65, $result->getWinPercentage(), 0.01);
    }

    public function testGetLossPercentageReturnsCorrectValue(): void
    {
        $result = new OddsResult(
            winProbability: 0.4865,
            expectedValue: 0.0,
            houseEdge: 0.0,
            betAmount: 10.0,
            winningNumbersCount: 18,
            totalNumbers: 37,
            payoutRatio: 1,
        );

        self::assertEqualsWithDelta(51.35, $result->getLossPercentage(), 0.01);
    }

    public function testGetHouseEdgePercentageReturnsCorrectValue(): void
    {
        $result = new OddsResult(
            winProbability: 0.4865,
            expectedValue: 0.0,
            houseEdge: 0.027,
            betAmount: 10.0,
            winningNumbersCount: 18,
            totalNumbers: 37,
            payoutRatio: 1,
        );

        self::assertEqualsWithDelta(2.7, $result->getHouseEdgePercentage(), 0.01);
    }

    public function testGetExpectedProfitReturnsExpectedValue(): void
    {
        $result = new OddsResult(
            winProbability: 0.4865,
            expectedValue: -0.27,
            houseEdge: 0.027,
            betAmount: 10.0,
            winningNumbersCount: 18,
            totalNumbers: 37,
            payoutRatio: 1,
        );

        self::assertSame(-0.27, $result->getExpectedProfit());
    }

    public function testGetExpectedPayoutIncludesOriginalStake(): void
    {
        $result = new OddsResult(
            winProbability: 0.4865,
            expectedValue: -0.27,
            houseEdge: 0.027,
            betAmount: 10.0,
            winningNumbersCount: 18,
            totalNumbers: 37,
            payoutRatio: 1,
        );

        self::assertEqualsWithDelta(9.73, $result->getExpectedPayout(), 0.01);
    }

    public function testGetExpectedProfitPercentageCalculatesCorrectly(): void
    {
        $result = new OddsResult(
            winProbability: 0.4865,
            expectedValue: -0.27,
            houseEdge: 0.027,
            betAmount: 10.0,
            winningNumbersCount: 18,
            totalNumbers: 37,
            payoutRatio: 1,
        );

        self::assertEqualsWithDelta(-2.7, $result->getExpectedProfitPercentage(), 0.01);
    }

    public function testGetExpectedProfitPercentageHandlesZeroBetAmount(): void
    {
        $result = new OddsResult(
            winProbability: 0.5,
            expectedValue: 0.0,
            houseEdge: 0.0,
            betAmount: 0.0,
            winningNumbersCount: 1,
            totalNumbers: 2,
            payoutRatio: 1,
        );

        self::assertSame(0.0, $result->getExpectedProfitPercentage());
    }

    public function testGetWinPayoutCalculatesCorrectly(): void
    {
        $result = new OddsResult(
            winProbability: 0.027027,
            expectedValue: -0.27,
            houseEdge: 0.027,
            betAmount: 10.0,
            winningNumbersCount: 1,
            totalNumbers: 37,
            payoutRatio: 35,
        );

        self::assertEqualsWithDelta(360.0, $result->getWinPayout(), 0.01);
    }

    public function testGetWinProfitCalculatesCorrectly(): void
    {
        $result = new OddsResult(
            winProbability: 0.027027,
            expectedValue: -0.27,
            houseEdge: 0.027,
            betAmount: 10.0,
            winningNumbersCount: 1,
            totalNumbers: 37,
            payoutRatio: 35,
        );

        self::assertEqualsWithDelta(350.0, $result->getWinProfit(), 0.01);
    }

    public function testToArrayReturnsAllData(): void
    {
        $result = new OddsResult(
            winProbability: 0.4865,
            expectedValue: -0.27,
            houseEdge: 0.027,
            betAmount: 10.0,
            winningNumbersCount: 18,
            totalNumbers: 37,
            payoutRatio: 1,
        );

        $array = $result->toArray();

        self::assertSame(0.4865, $array['win_probability']);
        self::assertEqualsWithDelta(0.5135, $array['loss_probability'], 0.0001);
        self::assertSame(-0.27, $array['expected_value']);
        self::assertSame(0.027, $array['house_edge']);
        self::assertSame(10.0, $array['bet_amount']);
        self::assertSame(18, $array['winning_numbers']);
        self::assertSame(37, $array['total_numbers']);
        self::assertSame(1, $array['payout_ratio']);
    }

    public function testToPercentageArrayReturnsPercentagesAndDetails(): void
    {
        $result = new OddsResult(
            winProbability: 0.4865,
            expectedValue: -0.27,
            houseEdge: 0.027,
            betAmount: 10.0,
            winningNumbersCount: 18,
            totalNumbers: 37,
            payoutRatio: 1,
        );

        $array = $result->toPercentageArray();

        self::assertEqualsWithDelta(48.65, $array['win_pct'], 0.01);
        self::assertEqualsWithDelta(51.35, $array['loss_pct'], 0.01);
        self::assertSame(-0.27, $array['expected_value']);
        self::assertEqualsWithDelta(-2.7, $array['expected_profit_pct'], 0.01);
        self::assertEqualsWithDelta(2.7, $array['house_edge_pct'], 0.01);
        self::assertSame(10.0, $array['bet_amount']);
        self::assertSame(18, $array['winning_numbers']);
        self::assertSame(37, $array['total_numbers']);
        self::assertSame(1, $array['payout_ratio']);
        self::assertEqualsWithDelta(20.0, $array['win_payout'], 0.01);
        self::assertEqualsWithDelta(10.0, $array['win_profit'], 0.01);
    }

    public function testEdgeCaseZeroWinProbability(): void
    {
        $result = new OddsResult(
            winProbability: 0.0,
            expectedValue: -10.0,
            houseEdge: 1.0,
            betAmount: 10.0,
            winningNumbersCount: 0,
            totalNumbers: 37,
            payoutRatio: 35,
        );

        self::assertSame(0.0, $result->getWinPercentage());
        self::assertSame(100.0, $result->getLossPercentage());
        self::assertSame(1.0, $result->getLossProbability());
    }

    public function testEdgeCaseFullWinProbability(): void
    {
        $result = new OddsResult(
            winProbability: 1.0,
            expectedValue: 10.0,
            houseEdge: 0.0,
            betAmount: 10.0,
            winningNumbersCount: 37,
            totalNumbers: 37,
            payoutRatio: 1,
        );

        self::assertSame(100.0, $result->getWinPercentage());
        self::assertSame(0.0, $result->getLossPercentage());
        self::assertSame(0.0, $result->getLossProbability());
    }
}
