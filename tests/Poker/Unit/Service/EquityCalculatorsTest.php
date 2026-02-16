<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Tests\Poker\Unit;

use Ecourty\PHPCasino\Common\Model\Card;
use Ecourty\PHPCasino\Poker\Model\PlayerRange;
use Ecourty\PHPCasino\Poker\Service\EquityCalculator\EnumerationCalculator;
use Ecourty\PHPCasino\Poker\Service\EquityCalculator\MonteCarloSimulator;
use Ecourty\PHPCasino\Poker\Service\HandEvaluator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(MonteCarloSimulator::class)]
#[CoversClass(EnumerationCalculator::class)]
class EquityCalculatorsTest extends TestCase
{
    private HandEvaluator $handEvaluator;
    private MonteCarloSimulator $monteCarloSimulator;
    private EnumerationCalculator $enumerationCalculator;

    protected function setUp(): void
    {
        $this->handEvaluator = new HandEvaluator();
        $this->monteCarloSimulator = new MonteCarloSimulator($this->handEvaluator);
        $this->enumerationCalculator = new EnumerationCalculator($this->handEvaluator);
    }

    // ========== PREFLOP TESTS (Monte Carlo Only) ==========

    /**
     * @param array<string> $heroCards
     * @param array<string> $opponentCards
     */
    #[DataProvider('headsUpPreflopProvider')]
    public function testHeadsUpPreflop(array $heroCards, array $opponentCards, float $minExpectedEquity, float $maxExpectedEquity, string $scenario): void
    {
        $hero = array_map(static fn (string $c): Card => Card::fromString($c), $heroCards);
        $opponent = array_map(static fn (string $c): Card => Card::fromString($c), $opponentCards);
        $opponentRange = PlayerRange::fromSpecificHand($opponent);

        $mcStats = $this->monteCarloSimulator->simulate($hero, [$opponentRange], [], 50000);
        $mcEquity = $mcStats['wins'] / $mcStats['total'];

        $this->assertGreaterThanOrEqual($minExpectedEquity, $mcEquity, "Equity too low for: $scenario");
        $this->assertLessThanOrEqual($maxExpectedEquity, $mcEquity, "Equity too high for: $scenario");
        $this->assertSame(50000, $mcStats['total'], 'Should run exactly 50k iterations');
    }

    // ========== POSTFLOP TESTS (Both Methods) ==========

    /**
     * @param array<string> $heroCards
     * @param array<string> $opponentCards
     * @param array<string> $boardCards
     */
    #[DataProvider('headsUpPostflopProvider')]
    public function testHeadsUpPostflop(array $heroCards, array $opponentCards, array $boardCards, float $minExpectedEquity, float $maxExpectedEquity, string $scenario): void
    {
        $hero = array_map(static fn (string $c): Card => Card::fromString($c), $heroCards);
        $opponent = array_map(static fn (string $c): Card => Card::fromString($c), $opponentCards);
        $board = array_map(static fn (string $c): Card => Card::fromString($c), $boardCards);
        $opponentRange = PlayerRange::fromSpecificHand($opponent);

        // Monte Carlo
        $mcStats = $this->monteCarloSimulator->simulate($hero, [$opponentRange], $board, 10000);
        $mcEquity = $mcStats['wins'] / $mcStats['total'];

        // Enumeration
        $enumStats = $this->enumerationCalculator->calculate($hero, [$opponentRange], $board);
        $enumEquity = $enumStats['wins'] / $enumStats['total'];

        // Both methods should agree within 5%
        $this->assertEqualsWithDelta($enumEquity, $mcEquity, 0.05, "MC and Enum disagree on: $scenario");

        // Enumeration should be in expected range
        $this->assertGreaterThanOrEqual($minExpectedEquity, $enumEquity, "Enum equity too low for: $scenario");
        $this->assertLessThanOrEqual($maxExpectedEquity, $enumEquity, "Enum equity too high for: $scenario");
    }

    // ========== COMPLETE BOARD TESTS (Deterministic) ==========

    /**
     * @param array<string> $heroCards
     * @param array<string> $opponentCards
     * @param array<string> $boardCards
     */
    #[DataProvider('completeBoardProvider')]
    public function testCompleteBoard(array $heroCards, array $opponentCards, array $boardCards, float $expectedWinRate, float $expectedTieRate, string $scenario): void
    {
        $hero = array_map(static fn (string $c): Card => Card::fromString($c), $heroCards);
        $opponent = array_map(static fn (string $c): Card => Card::fromString($c), $opponentCards);
        $board = array_map(static fn (string $c): Card => Card::fromString($c), $boardCards);
        $opponentRange = PlayerRange::fromSpecificHand($opponent);

        // Monte Carlo
        $mcStats = $this->monteCarloSimulator->simulate($hero, [$opponentRange], $board, 10000);
        $mcWinRate = $mcStats['wins'] / $mcStats['total'];
        $mcTieRate = $mcStats['ties'] / $mcStats['total'];

        // Enumeration
        $enumStats = $this->enumerationCalculator->calculate($hero, [$opponentRange], $board);
        $enumWinRate = $enumStats['wins'] / $enumStats['total'];
        $enumTieRate = $enumStats['ties'] / $enumStats['total'];

        // Complete board = deterministic
        $this->assertEquals($expectedWinRate, $enumWinRate, "Enum win rate wrong for: $scenario");
        $this->assertEquals($expectedTieRate, $enumTieRate, "Enum tie rate wrong for: $scenario");
        $this->assertEqualsWithDelta($expectedWinRate, $mcWinRate, 0.01, "MC disagrees on win rate for: $scenario");
        $this->assertEqualsWithDelta($expectedTieRate, $mcTieRate, 0.01, "MC disagrees on tie rate for: $scenario");
    }

    // ========== MULTI-WAY TESTS (3+ Players) ==========

    /**
     * @param array<string> $heroCards
     * @param array<array<string>> $opponentRanges
     * @param array<string> $boardCards
     */
    #[DataProvider('multiWayProvider')]
    public function testMultiWay(array $heroCards, array $opponentRanges, array $boardCards, float $minExpectedEquity, float $maxExpectedEquity, string $scenario): void
    {
        $hero = array_map(static fn (string $c): Card => Card::fromString($c), $heroCards);
        $board = array_map(static fn (string $c): Card => Card::fromString($c), $boardCards);

        $ranges = array_map(
            static fn (array $cards): PlayerRange => PlayerRange::fromSpecificHand(
                array_map(static fn (string $c): Card => Card::fromString($c), $cards),
            ),
            $opponentRanges,
        );

        $mcStats = $this->monteCarloSimulator->simulate($hero, $ranges, $board, 20000);
        $mcEquity = $mcStats['wins'] / $mcStats['total'];

        $this->assertGreaterThanOrEqual($minExpectedEquity, $mcEquity, "Equity too low for: $scenario");
        $this->assertLessThanOrEqual($maxExpectedEquity, $mcEquity, "Equity too high for: $scenario");
        $this->assertSame(20000, $mcStats['total']);
        $this->assertGreaterThan(0, $mcStats['wins'] + $mcStats['ties'], 'Should have some equity');
    }

    // ========== VALIDATION ==========

    public function testMonteCarloReturnsCorrectKeys(): void
    {
        $heroCards = [Card::fromString('As'), Card::fromString('Ah')];
        $opponentRange = PlayerRange::fromSpecificHand([Card::fromString('Ks'), Card::fromString('Kh')]);

        $mcStats = $this->monteCarloSimulator->simulate($heroCards, [$opponentRange], [], 1000);

        $this->assertArrayHasKey('wins', $mcStats);
        $this->assertArrayHasKey('ties', $mcStats);
        $this->assertArrayHasKey('total', $mcStats);
        $this->assertIsInt($mcStats['wins']);
        $this->assertIsInt($mcStats['ties']);
        $this->assertIsInt($mcStats['total']);
    }

    public function testEnumerationReturnsCorrectKeys(): void
    {
        $heroCards = [Card::fromString('As'), Card::fromString('Ah')];
        $opponentRange = PlayerRange::fromSpecificHand([Card::fromString('Ks'), Card::fromString('Kh')]);
        $board = [Card::fromString('Qs'), Card::fromString('Jh'), Card::fromString('10d')];

        $enumStats = $this->enumerationCalculator->calculate($heroCards, [$opponentRange], $board);

        $this->assertArrayHasKey('wins', $enumStats);
        $this->assertArrayHasKey('ties', $enumStats);
        $this->assertArrayHasKey('total', $enumStats);
        $this->assertIsInt($enumStats['wins']);
        $this->assertIsInt($enumStats['ties']);
        $this->assertIsInt($enumStats['total']);
    }

    public function testWinsPlusTiesPlusLossesEqualsTotal(): void
    {
        $heroCards = [Card::fromString('As'), Card::fromString('Ah')];
        $opponentRange = PlayerRange::fromSpecificHand([Card::fromString('Ks'), Card::fromString('Kh')]);

        $mcStats = $this->monteCarloSimulator->simulate($heroCards, [$opponentRange], [], 10000);
        $losses = $mcStats['total'] - $mcStats['wins'] - $mcStats['ties'];

        $this->assertSame($mcStats['total'], $mcStats['wins'] + $mcStats['ties'] + $losses);
        $this->assertGreaterThanOrEqual(0, $mcStats['wins']);
        $this->assertGreaterThanOrEqual(0, $mcStats['ties']);
        $this->assertGreaterThanOrEqual(0, $losses);
    }

    /**
     * @return array<string, array{0: array<string>, 1: array<string>, 2: float, 3: float, 4: string}>
     */
    public static function headsUpPreflopProvider(): array
    {
        return [
            'AA vs KK (Overpair)' => [['As', 'Ah'], ['Ks', 'Kh'], 0.80, 0.85, 'AA vs KK'],
            'AA vs AK (Domination)' => [['As', 'Ah'], ['Ad', 'Kh'], 0.91, 0.95, 'AA vs AK'],
            'AK vs QQ (Classic flip)' => [['As', 'Kh'], ['Qc', 'Qd'], 0.41, 0.47, 'AK vs QQ'],
            '22 vs AK (Pair vs overs)' => [['2s', '2h'], ['As', 'Kh'], 0.49, 0.54, '22 vs AK'],
            'AK vs AQ (Domination)' => [['As', 'Kh'], ['Ad', 'Qh'], 0.71, 0.76, 'AK vs AQ'],
            'KK vs QQ' => [['Ks', 'Kh'], ['Qc', 'Qd'], 0.80, 0.85, 'KK vs QQ'],
            'JJ vs 10-10' => [['Js', 'Jh'], ['10c', '10d'], 0.80, 0.85, 'JJ vs 10-10'],
            'AQ vs AJ (Kicker domination)' => [['As', 'Qh'], ['Ad', 'Jh'], 0.71, 0.76, 'AQ vs AJ'],
        ];
    }

    /**
     * @return array<string, array{0: array<string>, 1: array<string>, 2: array<string>, 3: float, 4: float, 5: string}>
     */
    public static function headsUpPostflopProvider(): array
    {
        return [
            'Top pair vs overpair' => [['As', 'Kh'], ['Qc', 'Qd'], ['Ah', '5h', '2c'], 0.89, 0.95, 'Top pair vs QQ'],
            'Set vs two pair' => [['5s', '5d'], ['As', 'Kh'], ['5h', 'Ah', 'Kc'], 0.75, 0.82, 'Set vs two pair'],
            'Flush draw vs pair' => [['Ah', 'Kh'], ['Qc', 'Qd'], ['10h', '5h', '2c'], 0.50, 0.58, 'Flush draw vs QQ'],
            'Made flush vs straight' => [['Ah', 'Kh'], ['Jd', '9c'], ['Qh', '10h', '8h'], 0.95, 1.0, 'Flush vs straight'],
            'Overpair vs underpair' => [['Ks', 'Kh'], ['Jc', 'Jd'], ['9h', '5s', '2c'], 0.89, 0.95, 'KK vs JJ'],
            'Top two vs bottom set' => [['As', 'Kh'], ['2c', '2d'], ['Ah', 'Kc', '2h'], 0.08, 0.22, 'Two pair vs set'],
            'Nut flush draw vs top pair' => [['As', 'Ks'], ['Ah', 'Qd'], ['10s', '7s', '2h'], 0.85, 0.95, 'NFD vs top pair'],
            'Trips vs two pair' => [['10s', '10d'], ['As', 'Kh'], ['10h', 'Ah', '5c'], 0.88, 0.94, 'Trips vs two pair'],
        ];
    }

    /**
     * @return array<string, array{0: array<string>, 1: array<string>, 2: array<string>, 3: float, 4: float, 5: string}>
     */
    public static function completeBoardProvider(): array
    {
        return [
            'Royal flush wins' => [['As', 'Ks'], ['2h', '3h'], ['Qs', 'Js', '10s', '9s', '8s'], 1.0, 0.0, 'Royal flush'],
            'SF beats quads' => [['9h', '8h'], ['As', 'Ah'], ['7h', '6h', '5h', 'Ad', 'Ac'], 1.0, 0.0, 'SF vs quads'],
            'Quads beat FH' => [['As', 'Ah'], ['Ks', 'Kh'], ['Ad', 'Ac', 'Kd', '5h', '2c'], 1.0, 0.0, 'Quads vs FH'],
            'FH beats flush' => [['Ks', 'Kh'], ['Ah', '10h'], ['Kd', '5h', '5c', '3h', '2h'], 1.0, 0.0, 'FH vs flush'],
            'Board straight' => [['As', '2h'], ['Kd', '3c'], ['Qs', 'Jh', '10d', '9c', '8h'], 0.0, 0.0, 'Both lose to board'],
            'Quads on board tie' => [['As', 'Kh'], ['Qd', 'Jc'], ['10s', '10h', '10d', '10c', '9h'], 1.0, 0.0, 'Hero wins A kicker'],
            'Higher kicker wins' => [['As', '2h'], ['Kd', '3c'], ['Qs', 'Qh', 'Jd', '10c', '9h'], 0.0, 0.0, 'Opp wins'],
        ];
    }

    /**
     * @return array<string, array{0: array<string>, 1: array<array<string>>, 2: array<string>, 3: float, 4: float, 5: string}>
     */
    public static function multiWayProvider(): array
    {
        return [
            'AA vs KK vs QQ' => [['As', 'Ah'], [['Ks', 'Kh'], ['Qc', 'Qd']], [], 0.63, 0.69, 'AA 3-way'],
            'AA vs 2 random' => [['As', 'Ah'], [['Kd', 'Qh'], ['Jc', '10d']], [], 0.58, 0.75, 'AA vs randoms'],
            'AK vs AA vs AQ' => [['As', 'Kh'], [['Ad', 'Ac'], ['Ah', 'Qd']], [], 0.04, 0.12, 'AK crushed'],
            'Set vs 2 opponents' => [['5s', '5d'], [['As', 'Kh'], ['Qc', 'Qd']], ['5h', '10d', '2c'], 0.85, 0.95, 'Set 3-way'],
            'Flush vs straights' => [['Ah', 'Kh'], [['Jd', '9c'], ['Qd', '10s']], ['Qh', '10h', '8h'], 0.65, 0.85, 'Flush vs 2'],
        ];
    }
}
