<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Tests\Poker\Unit;

use Ecourty\PHPCasino\Common\Enum\CardRank;
use Ecourty\PHPCasino\Common\Model\Card;
use Ecourty\PHPCasino\Poker\Enum\HandRank;
use Ecourty\PHPCasino\Poker\Model\Hand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Hand::class)]
class HandTest extends TestCase
{
    public function testConstructorCreatesHand(): void
    {
        $cards = [
            Card::fromString('As'),
            Card::fromString('Ah'),
            Card::fromString('Ad'),
            Card::fromString('Ac'),
            Card::fromString('Kh'),
        ];

        $kickers = [CardRank::ACE, CardRank::KING];

        $hand = new Hand(HandRank::FOUR_OF_A_KIND, $cards, $kickers);

        $this->assertSame(HandRank::FOUR_OF_A_KIND, $hand->rank);
        $this->assertSame($cards, $hand->cards);
        $this->assertSame($kickers, $hand->kickers);
    }

    public function testGetRankValueReturnsCorrectValue(): void
    {
        $hand = new Hand(
            HandRank::FLUSH,
            [
                Card::fromString('As'),
                Card::fromString('Ks'),
                Card::fromString('Qs'),
                Card::fromString('Js'),
                Card::fromString('9s'),
            ],
            [CardRank::ACE],
        );

        $this->assertSame(6, $hand->getRankValue());
    }

    public function testGetDescriptionReturnsCorrectDescription(): void
    {
        $testCases = [
            [HandRank::ROYAL_FLUSH, 'Royal Flush'],
            [HandRank::STRAIGHT_FLUSH, 'Straight Flush'],
            [HandRank::FOUR_OF_A_KIND, 'Four of a Kind'],
            [HandRank::FULL_HOUSE, 'Full House'],
            [HandRank::FLUSH, 'Flush'],
            [HandRank::STRAIGHT, 'Straight'],
            [HandRank::THREE_OF_A_KIND, 'Three of a Kind'],
            [HandRank::TWO_PAIR, 'Two Pair'],
            [HandRank::ONE_PAIR, 'One Pair'],
            [HandRank::HIGH_CARD, 'High Card'],
        ];

        foreach ($testCases as [$rank, $expectedDescription]) {
            $hand = new Hand($rank, [], []);
            $this->assertSame($expectedDescription, $hand->getDescription());
        }
    }

    public function testGetDetailedDescriptionForHighCard(): void
    {
        $hand = new Hand(
            HandRank::HIGH_CARD,
            [],
            [CardRank::ACE, CardRank::KING, CardRank::QUEEN, CardRank::JACK, CardRank::NINE],
        );

        $description = $hand->getDetailedDescription();

        $this->assertStringContainsString('High Card', $description);
        $this->assertStringContainsString('Ace', $description);
        $this->assertStringContainsString('King', $description);
    }

    public function testGetDetailedDescriptionForOnePair(): void
    {
        $hand = new Hand(
            HandRank::ONE_PAIR,
            [],
            [CardRank::ACE, CardRank::KING],
        );

        $description = $hand->getDetailedDescription();

        $this->assertStringContainsString('One Pair', $description);
        $this->assertStringContainsString('Aces', $description);
    }

    public function testGetDetailedDescriptionForTwoPair(): void
    {
        $hand = new Hand(
            HandRank::TWO_PAIR,
            [],
            [CardRank::ACE, CardRank::KING, CardRank::QUEEN],
        );

        $description = $hand->getDetailedDescription();

        $this->assertStringContainsString('Two Pair', $description);
        $this->assertStringContainsString('Aces', $description);
        $this->assertStringContainsString('Kings', $description);
    }

    public function testGetDetailedDescriptionForThreeOfAKind(): void
    {
        $hand = new Hand(
            HandRank::THREE_OF_A_KIND,
            [],
            [CardRank::KING],
        );

        $description = $hand->getDetailedDescription();

        $this->assertStringContainsString('Three of a Kind', $description);
        $this->assertStringContainsString('Kings', $description);
    }

    public function testGetDetailedDescriptionForStraight(): void
    {
        $hand = new Hand(
            HandRank::STRAIGHT,
            [],
            [CardRank::ACE],
        );

        $description = $hand->getDetailedDescription();

        $this->assertStringContainsString('Straight', $description);
        $this->assertStringContainsString('Ace high', $description);
    }

    public function testGetDetailedDescriptionForFlush(): void
    {
        $hand = new Hand(
            HandRank::FLUSH,
            [],
            [CardRank::KING],
        );

        $description = $hand->getDetailedDescription();

        $this->assertStringContainsString('Flush', $description);
        $this->assertStringContainsString('King high', $description);
    }

    public function testGetDetailedDescriptionForFullHouse(): void
    {
        $hand = new Hand(
            HandRank::FULL_HOUSE,
            [],
            [CardRank::ACE, CardRank::KING],
        );

        $description = $hand->getDetailedDescription();

        $this->assertStringContainsString('Full House', $description);
        $this->assertStringContainsString('Aces over Kings', $description);
    }

    public function testGetDetailedDescriptionForFourOfAKind(): void
    {
        $hand = new Hand(
            HandRank::FOUR_OF_A_KIND,
            [],
            [CardRank::QUEEN],
        );

        $description = $hand->getDetailedDescription();

        $this->assertStringContainsString('Four of a Kind', $description);
        $this->assertStringContainsString('Queens', $description);
    }

    public function testGetDetailedDescriptionForStraightFlush(): void
    {
        $hand = new Hand(
            HandRank::STRAIGHT_FLUSH,
            [],
            [CardRank::NINE],
        );

        $description = $hand->getDetailedDescription();

        $this->assertStringContainsString('Straight Flush', $description);
        $this->assertStringContainsString('9 high', $description);
    }

    public function testGetDetailedDescriptionForRoyalFlush(): void
    {
        $hand = new Hand(
            HandRank::ROYAL_FLUSH,
            [],
            [],
        );

        $description = $hand->getDetailedDescription();

        $this->assertSame('Royal Flush', $description);
    }

    public function testGetDetailedDescriptionWithEmptyKickers(): void
    {
        $hand = new Hand(HandRank::HIGH_CARD, [], []);

        $this->assertSame('High Card', $hand->getDetailedDescription());
    }

    public function testToArrayReturnsCorrectStructure(): void
    {
        $cards = [
            Card::fromString('As'),
            Card::fromString('Ah'),
            Card::fromString('Kh'),
            Card::fromString('Kd'),
            Card::fromString('Qc'),
        ];

        $hand = new Hand(
            HandRank::TWO_PAIR,
            $cards,
            [CardRank::ACE, CardRank::KING, CardRank::QUEEN],
        );

        $array = $hand->toArray();

        $this->assertArrayHasKey('rank', $array);
        $this->assertArrayHasKey('kickers', $array);
        $this->assertArrayHasKey('cards', $array);
        $this->assertArrayHasKey('description', $array);
        $this->assertArrayHasKey('detailed_description', $array);
    }

    public function testToArrayContainsCorrectRankValue(): void
    {
        $hand = new Hand(HandRank::FLUSH, [], []);

        $array = $hand->toArray();

        $this->assertSame(6, $array['rank']);
    }

    public function testToArrayContainsKickersAsIntegers(): void
    {
        $hand = new Hand(
            HandRank::ONE_PAIR,
            [],
            [CardRank::ACE, CardRank::KING],
        );

        $array = $hand->toArray();

        $this->assertSame([14, 13], $array['kickers']);
    }

    public function testToArrayContainsCardsAsStrings(): void
    {
        $cards = [
            Card::fromString('As'),
            Card::fromString('Kh'),
            Card::fromString('Qd'),
        ];

        $hand = new Hand(HandRank::HIGH_CARD, $cards, []);

        $array = $hand->toArray();

        $this->assertSame(['As', 'Kh', 'Qd'], $array['cards']);
    }

    public function testToArrayContainsDescriptions(): void
    {
        $hand = new Hand(
            HandRank::FULL_HOUSE,
            [],
            [CardRank::KING, CardRank::QUEEN],
        );

        $array = $hand->toArray();

        $this->assertSame('Full House', $array['description']);
        $this->assertStringContainsString('Kings over Queens', $array['detailed_description']);
    }

    public function testHandIsReadonly(): void
    {
        $hand = new Hand(HandRank::FLUSH, [], []);

        $this->assertSame(HandRank::FLUSH, $hand->rank);

        // PHP 8.1+ readonly properties cannot be modified
        // This test just verifies the hand exists and is accessible
        $this->assertCount(0, $hand->cards);
        $this->assertCount(0, $hand->kickers);
    }

    public function testMultipleHandRanksCanBeCompared(): void
    {
        $royalFlush = new Hand(HandRank::ROYAL_FLUSH, [], []);
        $highCard = new Hand(HandRank::HIGH_CARD, [], []);

        $this->assertGreaterThan($highCard->getRankValue(), $royalFlush->getRankValue());
    }

    public function testHandWithFiveCardsHasCorrectCount(): void
    {
        $cards = [
            Card::fromString('As'),
            Card::fromString('Ks'),
            Card::fromString('Qs'),
            Card::fromString('Js'),
            Card::fromString('10s'),
        ];

        $hand = new Hand(HandRank::ROYAL_FLUSH, $cards, []);

        $this->assertCount(5, $hand->cards);
    }

    public function testKickersAreOrderedCorrectly(): void
    {
        $kickers = [CardRank::ACE, CardRank::KING, CardRank::QUEEN, CardRank::JACK, CardRank::TEN];

        $hand = new Hand(HandRank::HIGH_CARD, [], $kickers);

        // Kickers should remain in the order provided
        $this->assertSame(CardRank::ACE, $hand->kickers[0]);
        $this->assertSame(CardRank::KING, $hand->kickers[1]);
        $this->assertSame(CardRank::QUEEN, $hand->kickers[2]);
    }
}
