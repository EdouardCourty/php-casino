<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Tests\Unit\Blackjack\Model;

use Ecourty\PHPCasino\Blackjack\Exception\InvalidHandException;
use Ecourty\PHPCasino\Blackjack\Model\Hand;
use Ecourty\PHPCasino\Common\Enum\CardRank;
use Ecourty\PHPCasino\Common\Enum\CardSuit;
use Ecourty\PHPCasino\Common\Model\Card;
use PHPUnit\Framework\TestCase;

final class HandTest extends TestCase
{
    public function testEmptyHandCreation(): void
    {
        $hand = new Hand();

        $this->assertTrue($hand->isEmpty());
        $this->assertSame(0, $hand->getCardCount());
        $this->assertEmpty($hand->getCards());
    }

    public function testAddCard(): void
    {
        $hand = new Hand();
        $card = new Card(CardRank::ACE, CardSuit::SPADES);

        $hand->addCard($card);

        $this->assertFalse($hand->isEmpty());
        $this->assertSame(1, $hand->getCardCount());
        $this->assertCount(1, $hand->getCards());
    }

    public function testFromCards(): void
    {
        $card1 = new Card(CardRank::ACE, CardSuit::SPADES);
        $card2 = new Card(CardRank::KING, CardSuit::HEARTS);

        $hand = Hand::fromCards($card1, $card2);

        $this->assertSame(2, $hand->getCardCount());
    }

    public function testCanSplitWithPair(): void
    {
        $hand = Hand::fromCards(
            new Card(CardRank::EIGHT, CardSuit::SPADES),
            new Card(CardRank::EIGHT, CardSuit::HEARTS)
        );

        $this->assertTrue($hand->canSplit());
    }

    public function testCannotSplitWithDifferentRanks(): void
    {
        $hand = Hand::fromCards(
            new Card(CardRank::EIGHT, CardSuit::SPADES),
            new Card(CardRank::SEVEN, CardSuit::HEARTS)
        );

        $this->assertFalse($hand->canSplit());
    }

    public function testCannotSplitWithThreeCards(): void
    {
        $hand = Hand::fromCards(
            new Card(CardRank::EIGHT, CardSuit::SPADES),
            new Card(CardRank::EIGHT, CardSuit::HEARTS),
            new Card(CardRank::EIGHT, CardSuit::CLUBS)
        );

        $this->assertFalse($hand->canSplit());
    }

    public function testCanDoubleDownWithTwoCards(): void
    {
        $hand = Hand::fromCards(
            new Card(CardRank::TEN, CardSuit::SPADES),
            new Card(CardRank::ACE, CardSuit::HEARTS)
        );

        $this->assertTrue($hand->canDoubleDown());
    }

    public function testCannotDoubleDownWithThreeCards(): void
    {
        $hand = Hand::fromCards(
            new Card(CardRank::TEN, CardSuit::SPADES),
            new Card(CardRank::ACE, CardSuit::HEARTS),
            new Card(CardRank::FIVE, CardSuit::CLUBS)
        );

        $this->assertFalse($hand->canDoubleDown());
    }

    public function testValidateNotEmptyThrowsOnEmptyHand(): void
    {
        $hand = new Hand();

        $this->expectException(InvalidHandException::class);
        $this->expectExceptionMessage('Hand cannot be empty.');

        $hand->validateNotEmpty();
    }

    public function testValidateCanSplitThrowsOnNonPair(): void
    {
        $hand = Hand::fromCards(
            new Card(CardRank::EIGHT, CardSuit::SPADES),
            new Card(CardRank::SEVEN, CardSuit::HEARTS)
        );

        $this->expectException(InvalidHandException::class);

        $hand->validateCanSplit();
    }

    public function testValidateCanDoubleDownThrowsOnThreeCards(): void
    {
        $hand = Hand::fromCards(
            new Card(CardRank::TEN, CardSuit::SPADES),
            new Card(CardRank::ACE, CardSuit::HEARTS),
            new Card(CardRank::FIVE, CardSuit::CLUBS)
        );

        $this->expectException(InvalidHandException::class);

        $hand->validateCanDoubleDown();
    }
}
