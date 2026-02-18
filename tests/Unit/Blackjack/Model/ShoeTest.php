<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Tests\Unit\Blackjack\Model;

use Ecourty\PHPCasino\Blackjack\Exception\InvalidShoeException;
use Ecourty\PHPCasino\Blackjack\Model\GameRules;
use Ecourty\PHPCasino\Blackjack\Model\Shoe;
use Ecourty\PHPCasino\Common\Model\Deck;
use PHPUnit\Framework\TestCase;

final class ShoeTest extends TestCase
{
    public function testCreateShoeWithSingleDeck(): void
    {
        $shoe = new Shoe(1);

        $this->assertSame(52, $shoe->getCardCount());
        $this->assertSame(1, $shoe->getDeckCount());
    }

    public function testCreateShoeWithSixDecks(): void
    {
        $shoe = new Shoe(6);

        $this->assertSame(312, $shoe->getCardCount());
        $this->assertSame(6, $shoe->getDeckCount());
    }

    public function testCreateShoeWithEightDecks(): void
    {
        $shoe = new Shoe(8);

        $this->assertSame(416, $shoe->getCardCount());
        $this->assertSame(8, $shoe->getDeckCount());
    }

    public function testThrowsExceptionForInvalidDeckCountTooLow(): void
    {
        $this->expectException(InvalidShoeException::class);
        $this->expectExceptionMessage('Invalid deck count: 0');

        new Shoe(0);
    }

    public function testThrowsExceptionForInvalidDeckCountTooHigh(): void
    {
        $this->expectException(InvalidShoeException::class);
        $this->expectExceptionMessage('Invalid deck count: 9');

        new Shoe(9);
    }

    public function testThrowsExceptionForInvalidPenetration(): void
    {
        $this->expectException(InvalidShoeException::class);
        $this->expectExceptionMessage('Invalid penetration: 1.5');

        new Shoe(6, 1.5);
    }

    public function testDrawSingleCard(): void
    {
        $shoe = new Shoe(1);
        $cards = $shoe->draw(1);

        $this->assertCount(1, $cards);
        $this->assertSame(51, $shoe->getCardCount());
        $this->assertSame(1, $shoe->getCardsDealt());
    }

    public function testDrawMultipleCards(): void
    {
        $shoe = new Shoe(1);
        $cards = $shoe->draw(5);

        $this->assertCount(5, $cards);
        $this->assertSame(47, $shoe->getCardCount());
        $this->assertSame(5, $shoe->getCardsDealt());
    }

    public function testThrowsExceptionWhenDrawingTooManyCards(): void
    {
        $shoe = new Shoe(1);

        $this->expectException(InvalidShoeException::class);
        $this->expectExceptionMessage('Not enough cards in shoe');

        $shoe->draw(53);
    }

    public function testShuffle(): void
    {
        $shoe = new Shoe(1);
        $originalCards = $shoe->getRemainingCards();

        $shoe->shuffle();

        $this->assertNotEquals($originalCards, $shoe->getRemainingCards());
        $this->assertSame(52, $shoe->getCardCount());
    }

    public function testReset(): void
    {
        $shoe = new Shoe(1);
        $shoe->draw(10);

        $this->assertSame(42, $shoe->getCardCount());
        $this->assertSame(10, $shoe->getCardsDealt());

        $shoe->reset();

        $this->assertSame(52, $shoe->getCardCount());
        $this->assertSame(0, $shoe->getCardsDealt());
    }

    public function testReshuffle(): void
    {
        $shoe = new Shoe(1);
        $shoe->draw(10);

        $this->assertSame(42, $shoe->getCardCount());

        $shoe->reshuffle();

        $this->assertSame(52, $shoe->getCardCount());
        $this->assertSame(0, $shoe->getCardsDealt());
    }

    public function testNeedsReshuffleBasedOnPenetration(): void
    {
        $shoe = new Shoe(1, 0.75);

        $this->assertFalse($shoe->needsReshuffle());

        $shoe->draw(38);
        $this->assertFalse($shoe->needsReshuffle());

        $shoe->draw(1);
        $this->assertTrue($shoe->needsReshuffle());
    }

    public function testGetCutCardPosition(): void
    {
        $shoe = new Shoe(6, 0.75);

        $expectedPosition = (int) (6 * 52 * 0.75);
        $this->assertSame($expectedPosition, $shoe->getCutCardPosition());
    }

    public function testIsEmpty(): void
    {
        $shoe = new Shoe(1);

        $this->assertFalse($shoe->isEmpty());

        $shoe->draw(52);

        $this->assertTrue($shoe->isEmpty());
    }

    public function testFromGameRules(): void
    {
        $rules = new GameRules(deckCount: 6, shoePenetration: 0.8);
        $shoe = Shoe::fromGameRules($rules);

        $this->assertSame(6, $shoe->getDeckCount());
        $this->assertSame(0.8, $shoe->getPenetration());
        $this->assertSame(312, $shoe->getCardCount());
    }

    public function testGetTotalCardCount(): void
    {
        $shoe = new Shoe(6);

        $this->assertSame(312, $shoe->getTotalCardCount());

        $shoe->draw(50);

        $this->assertSame(312, $shoe->getTotalCardCount());
        $this->assertSame(262, $shoe->getCardCount());
    }
}
