<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Tests\Poker\Unit;

use Ecourty\PHPCasino\Poker\Enum\EquityCalculationMethod;
use Ecourty\PHPCasino\Poker\Exception\InvalidEquityInputException;
use Ecourty\PHPCasino\Poker\Model\PlayerRange;
use Ecourty\PHPCasino\Poker\Service\EquityCalculator;
use Ecourty\PHPCasino\Poker\Service\EquityCalculator\EnumerationCalculator;
use Ecourty\PHPCasino\Poker\Service\EquityCalculator\MonteCarloSimulator;
use Ecourty\PHPCasino\Poker\Service\HandEvaluator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EquityCalculator::class)]
class EquityCalculatorTest extends TestCase
{
    private EquityCalculator $equityCalculator;

    protected function setUp(): void
    {
        $handEvaluator = new HandEvaluator();
        $monteCarloSimulator = new MonteCarloSimulator($handEvaluator);
        $enumerationCalculator = new EnumerationCalculator($handEvaluator);
        $this->equityCalculator = new EquityCalculator($monteCarloSimulator, $enumerationCalculator);
    }

    public function testMonteCarloWithAAPairVsKKPair(): void
    {
        $heroCards = ['As', 'Ah'];
        $opponentRange = PlayerRange::fromSpecificHand(['Ks', 'Kh']);

        $result = $this->equityCalculator->calculate(
            $heroCards,
            [$opponentRange],
            [],
            EquityCalculationMethod::MONTE_CARLO,
            10000,
        );

        // AA vs KK should win about 82% of the time
        $this->assertGreaterThan(0.75, $result->winProbability);
        $this->assertLessThan(0.90, $result->winProbability);
        $this->assertSame(10000, $result->iterations);
    }

    public function testEnumerationWithSpecificFlop(): void
    {
        $heroCards = ['As', 'Kh'];
        $opponentRange = PlayerRange::fromSpecificHand(['Qc', 'Qd']);
        $board = ['Ah', '5h', '2c'];

        $result = $this->equityCalculator->calculate(
            $heroCards,
            [$opponentRange],
            $board,
            EquityCalculationMethod::ENUMERATION,
        );

        // With top pair vs pocket queens, hero should be ahead
        $this->assertGreaterThan(0.5, $result->winProbability);
        $this->assertGreaterThan(0, $result->iterations);
    }

    public function testThrowsExceptionForInvalidHoleCardsCount(): void
    {
        $this->expectException(InvalidEquityInputException::class);
        $this->expectExceptionMessage('Hero must have exactly 2 hole cards');

        $opponentRange = PlayerRange::fromSpecificHand(['Ks', 'Kh']);
        $this->equityCalculator->calculate(['As'], [$opponentRange]);
    }

    public function testThrowsExceptionForTooManyCommunityCards(): void
    {
        $this->expectException(InvalidEquityInputException::class);
        $this->expectExceptionMessage('Community cards must be between 0 and 5');

        $opponentRange = PlayerRange::fromSpecificHand(['Ks', 'Kh']);
        $this->equityCalculator->calculate(
            ['As', 'Ah'],
            [$opponentRange],
            ['2h', '3h', '4h', '5h', '6h', '7h'],
        );
    }

    public function testThrowsExceptionForNoOpponents(): void
    {
        $this->expectException(InvalidEquityInputException::class);
        $this->expectExceptionMessage('At least one opponent range is required');

        $this->equityCalculator->calculate(['As', 'Ah'], []);
    }

    public function testThrowsExceptionForDuplicateCards(): void
    {
        $this->expectException(InvalidEquityInputException::class);
        $this->expectExceptionMessage('Duplicate card found in input: As');

        $opponentRange = PlayerRange::fromSpecificHand(['As', 'Kh']);
        $this->equityCalculator->calculate(['As', 'Ah'], [$opponentRange]);
    }

    public function testThrowsExceptionForDuplicateCardsInCommunity(): void
    {
        $this->expectException(InvalidEquityInputException::class);
        $this->expectExceptionMessage('Duplicate card found in input: As');

        $opponentRange = PlayerRange::fromSpecificHand(['Ks', 'Kh']);
        $this->equityCalculator->calculate(
            ['As', 'Ah'],
            [$opponentRange],
            ['2h', 'As'],
        );
    }

    public function testThrowsExceptionForInvalidIterations(): void
    {
        $this->expectException(InvalidEquityInputException::class);
        $this->expectExceptionMessage('Iterations must be positive');

        $opponentRange = PlayerRange::fromSpecificHand(['Ks', 'Kh']);
        $this->equityCalculator->calculate(
            ['As', 'Ah'],
            [$opponentRange],
            [],
            EquityCalculationMethod::MONTE_CARLO,
            0,
        );
    }

    public function testMultiWayPotWithThreePlayers(): void
    {
        $heroCards = ['As', 'Ah'];
        $opponent1 = PlayerRange::fromSpecificHand(['Ks', 'Kh']);
        $opponent2 = PlayerRange::fromSpecificHand(['Qc', 'Qd']);

        $result = $this->equityCalculator->calculate(
            $heroCards,
            [$opponent1, $opponent2],
            [],
            EquityCalculationMethod::MONTE_CARLO,
            5000,
        );

        // AA should still be favorite vs KK and QQ
        $this->assertGreaterThan(0.5, $result->winProbability);
        $this->assertSame(5000, $result->iterations);
    }
}
