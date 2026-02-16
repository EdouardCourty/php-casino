<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Tests\Poker\Unit;

use Ecourty\PHPCasino\Common\Model\Card;
use Ecourty\PHPCasino\Poker\Enum\HandRank;
use Ecourty\PHPCasino\Poker\Exception\NoValidHandFoundException;
use Ecourty\PHPCasino\Poker\Model\Hand;
use Ecourty\PHPCasino\Poker\Service\HandEvaluator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(HandEvaluator::class)]
class HandEvaluatorTest extends TestCase
{
    private HandEvaluator $evaluator;

    protected function setUp(): void
    {
        $this->evaluator = new HandEvaluator();
    }

    /**
     * @param array<string> $holeCards
     * @param array<string> $communityCards
     */
    #[DataProvider('royalFlushProvider')]
    public function testEvaluateRoyalFlush(array $holeCards, array $communityCards): void
    {
        $hand = $this->evaluator->evaluateBestHand($holeCards, $communityCards);

        $this->assertSame(HandRank::ROYAL_FLUSH, $hand->rank);
        $this->assertCount(5, $hand->kickers);
    }

    /**
     * @param array<string> $holeCards
     * @param array<string> $communityCards
     */
    #[DataProvider('straightflushProvider')]
    public function testEvaluateStraightFlush(array $holeCards, array $communityCards, int $expectedHighCard): void
    {
        $hand = $this->evaluator->evaluateBestHand($holeCards, $communityCards);

        $this->assertSame(HandRank::STRAIGHT_FLUSH, $hand->rank);
        $this->assertSame($expectedHighCard, $hand->kickers[0]->getValue());
    }

    /**
     * @param array<string> $holeCards
     * @param array<string> $communityCards
     */
    #[DataProvider('fourOfAKindProvider')]
    public function testEvaluateFourOfAKind(array $holeCards, array $communityCards, int $expectedQuadRank): void
    {
        $hand = $this->evaluator->evaluateBestHand($holeCards, $communityCards);

        $this->assertSame(HandRank::FOUR_OF_A_KIND, $hand->rank);
        $this->assertSame($expectedQuadRank, $hand->kickers[0]->getValue());
    }

    /**
     * @param array<string> $holeCards
     * @param array<string> $communityCards
     */
    #[DataProvider('fullHouseProvider')]
    public function testEvaluateFullHouse(array $holeCards, array $communityCards, int $expectedTripsRank, int $expectedPairRank): void
    {
        $hand = $this->evaluator->evaluateBestHand($holeCards, $communityCards);

        $this->assertSame(HandRank::FULL_HOUSE, $hand->rank);
        $this->assertSame($expectedTripsRank, $hand->kickers[0]->getValue());
        $this->assertSame($expectedPairRank, $hand->kickers[1]->getValue());
    }

    /**
     * @param array<string> $holeCards
     * @param array<string> $communityCards
     */
    #[DataProvider('flushProvider')]
    public function testEvaluateFlush(array $holeCards, array $communityCards): void
    {
        $hand = $this->evaluator->evaluateBestHand($holeCards, $communityCards);

        $this->assertSame(HandRank::FLUSH, $hand->rank);
        $this->assertCount(5, $hand->kickers);
    }

    /**
     * @param array<string> $holeCards
     * @param array<string> $communityCards
     */
    #[DataProvider('straightProvider')]
    public function testEvaluateStraight(array $holeCards, array $communityCards, int $expectedHighCard): void
    {
        $hand = $this->evaluator->evaluateBestHand($holeCards, $communityCards);

        $this->assertSame(HandRank::STRAIGHT, $hand->rank);
        // For wheel (A-2-3-4-5), the high card should be 5
        $this->assertSame($expectedHighCard, $hand->kickers[0]->getValue());
    }

    /**
     * @param array<string> $holeCards
     * @param array<string> $communityCards
     */
    #[DataProvider('threeOfAKindProvider')]
    public function testEvaluateThreeOfAKind(array $holeCards, array $communityCards, int $expectedTripsRank): void
    {
        $hand = $this->evaluator->evaluateBestHand($holeCards, $communityCards);

        $this->assertSame(HandRank::THREE_OF_A_KIND, $hand->rank);
        $this->assertSame($expectedTripsRank, $hand->kickers[0]->getValue());
    }

    /**
     * @param array<string> $holeCards
     * @param array<string> $communityCards
     */
    #[DataProvider('twoPairProvider')]
    public function testEvaluateTwoPair(array $holeCards, array $communityCards, int $expectedHighPair, int $expectedLowPair): void
    {
        $hand = $this->evaluator->evaluateBestHand($holeCards, $communityCards);

        $this->assertSame(HandRank::TWO_PAIR, $hand->rank);
        $this->assertSame($expectedHighPair, $hand->kickers[0]->getValue());
        $this->assertSame($expectedLowPair, $hand->kickers[1]->getValue());
    }

    /**
     * @param array<string> $holeCards
     * @param array<string> $communityCards
     */
    #[DataProvider('onePairProvider')]
    public function testEvaluateOnePair(array $holeCards, array $communityCards, int $expectedPairRank): void
    {
        $hand = $this->evaluator->evaluateBestHand($holeCards, $communityCards);

        $this->assertSame(HandRank::ONE_PAIR, $hand->rank);
        $this->assertSame($expectedPairRank, $hand->kickers[0]->getValue());
    }

    /**
     * @param array<string> $holeCards
     * @param array<string> $communityCards
     */
    #[DataProvider('highcardProvider')]
    public function testEvaluateHighCard(array $holeCards, array $communityCards, int $expectedHighCard): void
    {
        $hand = $this->evaluator->evaluateBestHand($holeCards, $communityCards);

        $this->assertSame(HandRank::HIGH_CARD, $hand->rank);
        $this->assertSame($expectedHighCard, $hand->kickers[0]->getValue());
    }

    #[DataProvider('compareHandsProvider')]
    public function testCompareHands(Hand $hand1, Hand $hand2, int $expectedResult): void
    {
        $result = $this->evaluator->compareHands($hand1, $hand2);

        if ($expectedResult > 0) {
            $this->assertGreaterThan(0, $result, 'Hand1 should be greater than Hand2');
        } elseif ($expectedResult < 0) {
            $this->assertLessThan(0, $result, 'Hand1 should be less than Hand2');
        } else {
            $this->assertSame(0, $result, 'Hands should be equal');
        }
    }

    public function testEvaluateBestHandWithLessThan5Cards(): void
    {
        $this->expectException(NoValidHandFoundException::class);
        $this->evaluator->evaluateBestHand(['Ah', 'Kh'], ['Qh']);
    }

    public function testEvaluateBestHandAcceptsCardObjects(): void
    {
        $hand = $this->evaluator->evaluateBestHand(
            [Card::fromString('Ah'), Card::fromString('Kh')],
            [Card::fromString('Qh'), Card::fromString('Jh'), Card::fromString('10h')],
        );

        $this->assertSame(HandRank::ROYAL_FLUSH, $hand->rank);
    }

    /**
     * @return array<string, array{0: array<string>, 1: array<string>}>
     */
    public static function royalFlushProvider(): array
    {
        return [
            'Hearts Royal Flush' => [
                ['Ah', 'Kh'],
                ['Qh', 'Jh', '10h', '9h', '2c'],
            ],
            'Spades Royal Flush' => [
                ['As', 'Ks'],
                ['Qs', 'Js', '10s', '3d', '7c'],
            ],
            'Diamonds Royal Flush' => [
                ['Ad', 'Kd'],
                ['Qd', 'Jd', '10d', '5s', '8h'],
            ],
            'Clubs Royal Flush' => [
                ['Ac', 'Kc'],
                ['Qc', 'Jc', '10c', '2h', '4s'],
            ],
        ];
    }

    /**
     * @return array<string, array{0: array<string>, 1: array<string>, 2: int}>
     */
    public static function straightFlushProvider(): array
    {
        return [
            '9-High Straight Flush' => [
                ['9h', '8h'],
                ['7h', '6h', '5h', 'Ac', '2d'],
                9,
            ],
            'King-High Straight Flush' => [
                ['Ks', 'Qs'],
                ['Js', '10s', '9s', '2h', '3c'],
                13,
            ],
            '6-High Straight Flush' => [
                ['6d', '5d'],
                ['4d', '3d', '2d', 'Kh', 'As'],
                6,
            ],
            'Wheel Straight Flush (5-high)' => [
                ['5c', '4c'],
                ['3c', '2c', 'Ac', 'Kd', '9s'],
                14, // Ace is high card in kickers even for wheel
            ],
        ];
    }

    /**
     * @return array<string, array{0: array<string>, 1: array<string>, 2: int}>
     */
    public static function fourOfAKindProvider(): array
    {
        return [
            'Four Aces' => [
                ['Ah', 'As'],
                ['Ad', 'Ac', 'Kh', 'Qs', '2c'],
                14,
            ],
            'Four Kings' => [
                ['Kh', 'Ks'],
                ['Kd', 'Kc', '9h', '7s', '3c'],
                13,
            ],
            'Four Twos' => [
                ['2h', '2s'],
                ['2d', '2c', 'Ah', 'Ks', 'Qc'],
                2,
            ],
            'Four Sevens' => [
                ['7h', '7s'],
                ['7d', '7c', 'Jh', '8s', '4c'],
                7,
            ],
        ];
    }

    /**
     * @return array<string, array{0: array<string>, 1: array<string>, 2: int, 3: int}>
     */
    public static function fullHouseProvider(): array
    {
        return [
            'Aces full of Kings' => [
                ['Ah', 'As'],
                ['Ad', 'Kh', 'Ks', '2c', '3d'],
                14,
                13,
            ],
            'Kings full of Queens' => [
                ['Kh', 'Ks'],
                ['Kd', 'Qh', 'Qs', '9c', '7d'],
                13,
                12,
            ],
            'Threes full of Twos' => [
                ['3h', '3s'],
                ['3d', '2h', '2s', 'Ac', 'Kd'],
                3,
                2,
            ],
            'Jacks full of Tens' => [
                ['Jh', 'Js'],
                ['Jd', '10h', '10s', '5c', '6d'],
                11,
                10,
            ],
        ];
    }

    /**
     * @return array<string, array{0: array<string>, 1: array<string>}>
     */
    public static function flushProvider(): array
    {
        return [
            'Ace-high Hearts Flush' => [
                ['Ah', 'Kh'],
                ['Qh', '9h', '7h', '3s', '2c'],
            ],
            'King-high Spades Flush' => [
                ['Ks', 'Js'],
                ['9s', '6s', '4s', 'Ah', '2d'],
            ],
            'Ten-high Diamonds Flush' => [
                ['10d', '8d'],
                ['6d', '5d', '3d', 'Kh', 'Qc'],
            ],
            'Queen-high Clubs Flush' => [
                ['Qc', 'Jc'],
                ['8c', '6c', '2c', 'As', 'Kh'],
            ],
        ];
    }

    /**
     * @return array<string, array{0: array<string>, 1: array<string>, 2: int}>
     */
    public static function straightProvider(): array
    {
        return [
            'Ace-high Straight (Broadway)' => [
                ['Ah', 'Ks'],
                ['Qd', 'Jc', '10h', '9s', '2c'],
                14,
            ],
            'King-high Straight' => [
                ['Kh', 'Qs'],
                ['Jd', '10c', '9h', '3s', '2c'],
                13,
            ],
            'Six-high Straight' => [
                ['6h', '5s'],
                ['4d', '3c', '2h', 'As', 'Kc'],
                14, // Best 5-card hand includes Ace-high straight
            ],
            'Wheel (5-high Straight with Ace)' => [
                ['5h', '4s'],
                ['3d', '2c', 'Ah', 'Ks', 'Qc'],
                14, // Ace is high card in kickers even for wheel
            ],
            'Seven-high Straight' => [
                ['7d', '6c'],
                ['5h', '4s', '3d', 'Ac', 'Kh'],
                7,
            ],
        ];
    }

    /**
     * @return array<string, array{0: array<string>, 1: array<string>, 2: int}>
     */
    public static function threeOfAKindProvider(): array
    {
        return [
            'Three Aces' => [
                ['Ah', 'As'],
                ['Ad', 'Kh', 'Qs', '9c', '2d'],
                14,
            ],
            'Three Kings' => [
                ['Kh', 'Ks'],
                ['Kd', 'Jh', '9s', '7c', '3d'],
                13,
            ],
            'Three Fives' => [
                ['5h', '5s'],
                ['5d', 'Ah', 'Ks', '8c', '2d'],
                5,
            ],
            'Three Twos' => [
                ['2h', '2s'],
                ['2d', 'Ah', 'Ks', 'Qc', 'Jd'],
                2,
            ],
        ];
    }

    /**
     * @return array<string, array{0: array<string>, 1: array<string>, 2: int, 3: int}>
     */
    public static function twoPairProvider(): array
    {
        return [
            'Aces and Kings' => [
                ['Ah', 'Ks'],
                ['Ad', 'Kh', 'Qs', '9c', '2d'],
                14,
                13,
            ],
            'Queens and Jacks' => [
                ['Qh', 'Js'],
                ['Qd', 'Jh', '9s', '7c', '3d'],
                12,
                11,
            ],
            'Tens and Nines' => [
                ['10h', '9s'],
                ['10d', '9h', 'As', 'Kc', '2d'],
                10,
                9,
            ],
            'Threes and Twos' => [
                ['3h', '2s'],
                ['3d', '2h', 'As', 'Kc', 'Qd'],
                3,
                2,
            ],
        ];
    }

    /**
     * @return array<string, array{0: array<string>, 1: array<string>, 2: int}>
     */
    public static function onePairProvider(): array
    {
        return [
            'Pair of Aces' => [
                ['Ah', 'As'],
                ['Kd', 'Qh', 'Js', '9c', '2d'],
                14,
            ],
            'Pair of Kings' => [
                ['Kh', 'Ks'],
                ['Ad', 'Qh', 'Js', '8c', '3d'],
                13,
            ],
            'Pair of Sevens' => [
                ['7h', '7s'],
                ['Ad', 'Kh', 'Qs', '9c', '2d'],
                7,
            ],
            'Pair of Twos' => [
                ['2h', '2s'],
                ['Ad', 'Kh', 'Qs', 'Jc', '9d'],
                2,
            ],
        ];
    }

    /**
     * @return array<string, array{0: array<string>, 1: array<string>, 2: int}>
     */
    public static function highCardProvider(): array
    {
        return [
            'Ace High' => [
                ['Ah', 'Ks'],
                ['Qd', 'Jc', '9h', '7s', '2c'],
                14,
            ],
            'King High' => [
                ['Kh', 'Qs'],
                ['Jd', '9c', '7h', '5s', '2c'],
                13,
            ],
            'Ten High' => [
                ['10h', '9s'],
                ['8d', '6c', '4h', '3s', '2c'],
                10,
            ],
            'Seven High' => [
                ['7h', '6s'],
                ['5d', '4c', '2h', 'As', 'Kc'],
                14, // Ace is present and is high card
            ],
        ];
    }

    /**
     * @return array<string, array{0: Hand, 1: Hand, 2: int}>
     */
    public static function compareHandsProvider(): array
    {
        return [
            'Royal Flush beats Straight Flush' => [
                new Hand(HandRank::ROYAL_FLUSH, [], []),
                new Hand(HandRank::STRAIGHT_FLUSH, [], []),
                1,
            ],
            'Four of a Kind beats Full House' => [
                new Hand(HandRank::FOUR_OF_A_KIND, [], []),
                new Hand(HandRank::FULL_HOUSE, [], []),
                1,
            ],
            'Full House beats Flush' => [
                new Hand(HandRank::FULL_HOUSE, [], []),
                new Hand(HandRank::FLUSH, [], []),
                1,
            ],
            'Flush beats Straight' => [
                new Hand(HandRank::FLUSH, [], []),
                new Hand(HandRank::STRAIGHT, [], []),
                1,
            ],
            'Straight beats Three of a Kind' => [
                new Hand(HandRank::STRAIGHT, [], []),
                new Hand(HandRank::THREE_OF_A_KIND, [], []),
                1,
            ],
            'Three of a Kind beats Two Pair' => [
                new Hand(HandRank::THREE_OF_A_KIND, [], []),
                new Hand(HandRank::TWO_PAIR, [], []),
                1,
            ],
            'Two Pair beats One Pair' => [
                new Hand(HandRank::TWO_PAIR, [], []),
                new Hand(HandRank::ONE_PAIR, [], []),
                1,
            ],
            'One Pair beats High Card' => [
                new Hand(HandRank::ONE_PAIR, [], []),
                new Hand(HandRank::HIGH_CARD, [], []),
                1,
            ],
            'Higher Kicker wins (Ace vs King)' => [
                new Hand(HandRank::HIGH_CARD, [], [Card::fromString('Ah')->rank, Card::fromString('Kh')->rank]),
                new Hand(HandRank::HIGH_CARD, [], [Card::fromString('Ks')->rank, Card::fromString('Qs')->rank]),
                1,
            ],
            'Equal hands (same rank and kickers)' => [
                new Hand(HandRank::ONE_PAIR, [], [Card::fromString('Ah')->rank]),
                new Hand(HandRank::ONE_PAIR, [], [Card::fromString('As')->rank]),
                0,
            ],
        ];
    }
}
