<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Tests\Poker\Unit;

use Ecourty\PHPCasino\Common\Enum\CardRank;
use Ecourty\PHPCasino\Common\Enum\CardSuit;
use Ecourty\PHPCasino\Common\Model\Card;
use Ecourty\PHPCasino\Poker\Exception\InvalidCardNotationException;
use Ecourty\PHPCasino\Poker\Exception\InvalidRankException;
use Ecourty\PHPCasino\Poker\Exception\InvalidSuitException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Card::class)]
class CardTest extends TestCase
{
    public function testConstructorCreatesCard(): void
    {
        $card = new Card(CardRank::ACE, CardSuit::SPADES);

        $this->assertSame(CardRank::ACE, $card->rank);
        $this->assertSame(CardSuit::SPADES, $card->suit);
    }

    public function testFromStringCreatesCardFromValidNotation(): void
    {
        $card = Card::fromString('As');

        $this->assertSame(CardRank::ACE, $card->rank);
        $this->assertSame(CardSuit::SPADES, $card->suit);
    }

    public function testFromStringWithAllRanks(): void
    {
        $notations = [
            '2h' => [CardRank::TWO, CardSuit::HEARTS],
            '3d' => [CardRank::THREE, CardSuit::DIAMONDS],
            '4c' => [CardRank::FOUR, CardSuit::CLUBS],
            '5s' => [CardRank::FIVE, CardSuit::SPADES],
            '6h' => [CardRank::SIX, CardSuit::HEARTS],
            '7d' => [CardRank::SEVEN, CardSuit::DIAMONDS],
            '8c' => [CardRank::EIGHT, CardSuit::CLUBS],
            '9s' => [CardRank::NINE, CardSuit::SPADES],
            '10h' => [CardRank::TEN, CardSuit::HEARTS],
            'Jd' => [CardRank::JACK, CardSuit::DIAMONDS],
            'Qc' => [CardRank::QUEEN, CardSuit::CLUBS],
            'Ks' => [CardRank::KING, CardSuit::SPADES],
            'Ah' => [CardRank::ACE, CardSuit::HEARTS],
        ];

        foreach ($notations as $notation => [$expectedRank, $expectedSuit]) {
            $card = Card::fromString($notation);
            $this->assertSame($expectedRank, $card->rank, "Failed for notation: {$notation}");
            $this->assertSame($expectedSuit, $card->suit, "Failed for notation: {$notation}");
        }
    }

    public function testFromStringWithAllSuits(): void
    {
        $suits = [
            'Ah' => CardSuit::HEARTS,
            'Ad' => CardSuit::DIAMONDS,
            'Ac' => CardSuit::CLUBS,
            'As' => CardSuit::SPADES,
        ];

        foreach ($suits as $notation => $expectedSuit) {
            $card = Card::fromString($notation);
            $this->assertSame($expectedSuit, $card->suit);
        }
    }

    public function testFromStringThrowsExceptionForTooShortString(): void
    {
        $this->expectException(InvalidCardNotationException::class);
        $this->expectExceptionMessage('too short');

        Card::fromString('A');
    }

    public function testFromStringThrowsExceptionForInvalidSuit(): void
    {
        $this->expectException(InvalidSuitException::class);

        Card::fromString('Ax'); // 'x' is not a valid suit
    }

    public function testFromStringThrowsExceptionForInvalidRank(): void
    {
        $this->expectException(InvalidRankException::class);

        Card::fromString('Zh'); // 'Z' is not a valid rank
    }

    public function testToStringReturnsCorrectNotation(): void
    {
        $card = new Card(CardRank::ACE, CardSuit::SPADES);

        $this->assertSame('As', $card->toString());
    }

    public function testToStringWithTen(): void
    {
        $card = new Card(CardRank::TEN, CardSuit::HEARTS);

        $this->assertSame('10h', $card->toString());
    }

    public function testMagicToStringMethod(): void
    {
        $card = new Card(CardRank::KING, CardSuit::DIAMONDS);

        $this->assertSame('Kd', (string) $card);
    }

    public function testEqualsReturnsTrueForIdenticalCards(): void
    {
        $card1 = new Card(CardRank::ACE, CardSuit::SPADES);
        $card2 = new Card(CardRank::ACE, CardSuit::SPADES);

        $this->assertTrue($card1->equals($card2));
    }

    public function testEqualsReturnsFalseForDifferentRanks(): void
    {
        $card1 = new Card(CardRank::ACE, CardSuit::SPADES);
        $card2 = new Card(CardRank::KING, CardSuit::SPADES);

        $this->assertFalse($card1->equals($card2));
    }

    public function testEqualsReturnsFalseForDifferentSuits(): void
    {
        $card1 = new Card(CardRank::ACE, CardSuit::SPADES);
        $card2 = new Card(CardRank::ACE, CardSuit::HEARTS);

        $this->assertFalse($card1->equals($card2));
    }

    public function testGetValueReturnsCorrectNumericValue(): void
    {
        $testCases = [
            [CardRank::TWO, 2],
            [CardRank::THREE, 3],
            [CardRank::FOUR, 4],
            [CardRank::FIVE, 5],
            [CardRank::SIX, 6],
            [CardRank::SEVEN, 7],
            [CardRank::EIGHT, 8],
            [CardRank::NINE, 9],
            [CardRank::TEN, 10],
            [CardRank::JACK, 11],
            [CardRank::QUEEN, 12],
            [CardRank::KING, 13],
            [CardRank::ACE, 14],
        ];

        foreach ($testCases as [$rank, $expectedValue]) {
            $card = new Card($rank, CardSuit::SPADES);
            $this->assertSame($expectedValue, $card->getValue());
        }
    }

    public function testGetNameReturnsCorrectNames(): void
    {
        $testCases = [
            [CardRank::TWO, '2'],
            [CardRank::JACK, 'Jack'],
            [CardRank::QUEEN, 'Queen'],
            [CardRank::KING, 'King'],
            [CardRank::ACE, 'Ace'],
        ];

        foreach ($testCases as [$rank, $expectedName]) {
            $card = new Card($rank, CardSuit::HEARTS);
            $this->assertSame($expectedName, $card->getName());
        }
    }

    public function testIsFaceCardReturnsTrueForFaceCards(): void
    {
        $faceCards = [CardRank::JACK, CardRank::QUEEN, CardRank::KING];

        foreach ($faceCards as $rank) {
            $card = new Card($rank, CardSuit::CLUBS);
            $this->assertTrue($card->isFaceCard(), "Failed for rank: {$rank->value}");
        }
    }

    public function testIsFaceCardReturnsFalseForNonFaceCards(): void
    {
        $nonFaceCards = [CardRank::TWO, CardRank::FIVE, CardRank::TEN, CardRank::ACE];

        foreach ($nonFaceCards as $rank) {
            $card = new Card($rank, CardSuit::CLUBS);
            $this->assertFalse($card->isFaceCard(), "Failed for rank: {$rank->value}");
        }
    }

    public function testIsNumericReturnsTrueForNumericCards(): void
    {
        $numericRanks = [
            CardRank::TWO, CardRank::THREE, CardRank::FOUR, CardRank::FIVE,
            CardRank::SIX, CardRank::SEVEN, CardRank::EIGHT, CardRank::NINE, CardRank::TEN,
        ];

        foreach ($numericRanks as $rank) {
            $card = new Card($rank, CardSuit::DIAMONDS);
            $this->assertTrue($card->isNumeric(), "Failed for rank: {$rank->value}");
        }
    }

    public function testIsNumericReturnsFalseForFaceCardsAndAce(): void
    {
        $nonNumericRanks = [CardRank::JACK, CardRank::QUEEN, CardRank::KING, CardRank::ACE];

        foreach ($nonNumericRanks as $rank) {
            $card = new Card($rank, CardSuit::DIAMONDS);
            $this->assertFalse($card->isNumeric(), "Failed for rank: {$rank->value}");
        }
    }

    public function testRoundTripConversion(): void
    {
        $originalNotation = 'Kh';
        $card = Card::fromString($originalNotation);
        $convertedNotation = $card->toString();

        $this->assertSame($originalNotation, $convertedNotation);
    }
}
