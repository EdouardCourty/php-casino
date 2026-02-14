<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Tests\Poker\Unit;

use Ecourty\PHPCasino\Poker\Exception\CardNotFoundException;
use Ecourty\PHPCasino\Poker\Exception\DuplicateCardException;
use Ecourty\PHPCasino\Poker\Exception\InvalidCardCountException;
use Ecourty\PHPCasino\Poker\Exception\NotEnoughCardsException;
use Ecourty\PHPCasino\Poker\Model\Card;
use Ecourty\PHPCasino\Poker\Model\Deck;
use PHPUnit\Framework\TestCase;

class DeckTest extends TestCase
{
    public function testConstructorCreates52Cards(): void
    {
        $deck = new Deck();

        $this->assertSame(52, $deck->count());
    }

    public function testResetRestoresDeckTo52Cards(): void
    {
        $deck = new Deck();
        $deck->draw(10);

        $this->assertSame(42, $deck->count());

        $deck->reset();

        $this->assertSame(52, $deck->count());
    }

    public function testShuffledCreatesNewShuffledDeck(): void
    {
        $deck = Deck::shuffled();

        $this->assertSame(52, $deck->count());
        // Note: We can't easily test if it's actually shuffled without making assumptions
        // about the shuffle algorithm or making the test flaky
    }

    public function testDrawRemovesCardsFromDeck(): void
    {
        $deck = new Deck();
        $cards = $deck->draw(5);

        $this->assertCount(5, $cards);
        $this->assertSame(47, $deck->count());
    }

    public function testDrawReturnsCardObjects(): void
    {
        $deck = new Deck();
        $cards = $deck->draw(3);

        foreach ($cards as $card) {
            $this->assertInstanceOf(Card::class, $card);
        }
    }

    public function testDrawZeroCardsReturnsEmptyArray(): void
    {
        $deck = new Deck();
        $cards = $deck->draw(0);

        $this->assertCount(0, $cards);
        $this->assertSame(52, $deck->count());
    }

    public function testDrawThrowsExceptionForNegativeCount(): void
    {
        $this->expectException(InvalidCardCountException::class);
        $this->expectExceptionMessage('negative');

        $deck = new Deck();
        $deck->draw(-1);
    }

    public function testDrawThrowsExceptionWhenNotEnoughCards(): void
    {
        $this->expectException(NotEnoughCardsException::class);

        $deck = new Deck();
        $deck->draw(53);
    }

    public function testBurnRemovesCardsFromDeck(): void
    {
        $deck = new Deck();
        $deck->burn(3);

        $this->assertSame(49, $deck->count());
    }

    public function testBurnReturnsFluentInterface(): void
    {
        $deck = new Deck();
        $result = $deck->burn(1);

        $this->assertSame($deck, $result);
    }

    public function testBurnThrowsExceptionForNegativeCount(): void
    {
        $this->expectException(InvalidCardCountException::class);

        $deck = new Deck();
        $deck->burn(-1);
    }

    public function testBurnThrowsExceptionWhenNotEnoughCards(): void
    {
        $this->expectException(NotEnoughCardsException::class);

        $deck = new Deck();
        $deck->burn(53);
    }

    public function testShuffleReturnsFluentInterface(): void
    {
        $deck = new Deck();
        $result = $deck->shuffle();

        $this->assertSame($deck, $result);
    }

    public function testShuffleChangesCardOrder(): void
    {
        $deck1 = new Deck();
        $deck2 = new Deck();

        $cards1Before = $deck1->draw(10);
        $deck1->reset();
        $deck1->shuffle();
        $cards1After = $deck1->draw(10);

        // Very unlikely that shuffled cards are in same order
        $differentCount = 0;
        for ($i = 0; $i < 10; $i++) {
            if (!$cards1Before[$i]->equals($cards1After[$i])) {
                $differentCount++;
            }
        }

        // At least some cards should be different (statistically almost certain)
        $this->assertGreaterThan(3, $differentCount, 'Shuffle should change card order');
    }

    public function testRemoveCardRemovesSpecificCard(): void
    {
        $deck = new Deck();
        $cardToRemove = Card::fromString('As');

        $deck->removeCard($cardToRemove);

        $this->assertSame(51, $deck->count());

        // Try to remove again - should throw
        $this->expectException(CardNotFoundException::class);
        $deck->removeCard($cardToRemove, throwIfNotFound: true);
    }

    public function testRemoveCardReturnsFluentInterface(): void
    {
        $deck = new Deck();
        $card = Card::fromString('Kh');

        $result = $deck->removeCard($card);

        $this->assertSame($deck, $result);
    }

    public function testRemoveCardThrowsExceptionIfCardNotFound(): void
    {
        $this->expectException(CardNotFoundException::class);

        $deck = new Deck();
        $card = Card::fromString('As');
        $deck->removeCard($card); // Remove once
        $deck->removeCard($card, throwIfNotFound: true); // Try to remove again
    }

    public function testRemoveCardDoesNotThrowWhenFlagIsFalse(): void
    {
        $deck = new Deck();
        $card = Card::fromString('As');
        $deck->removeCard($card); // Remove once

        // Should not throw
        $deck->removeCard($card, throwIfNotFound: false);

        $this->assertSame(51, $deck->count());
    }

    public function testRemoveCardsRemovesMultipleCards(): void
    {
        $deck = new Deck();
        $cards = [
            Card::fromString('As'),
            Card::fromString('Kh'),
            Card::fromString('Qd'),
        ];

        $deck->removeCards($cards);

        $this->assertSame(49, $deck->count());
    }

    public function testRemoveCardsReturnsFluentInterface(): void
    {
        $deck = new Deck();
        $cards = [Card::fromString('As'), Card::fromString('Kh')];

        $result = $deck->removeCards($cards);

        $this->assertSame($deck, $result);
    }

    public function testAddCardAddsCardToDeck(): void
    {
        $deck = new Deck();
        $card = Card::fromString('As');
        $deck->removeCard($card);

        $this->assertSame(51, $deck->count());

        $deck->addCard($card);

        $this->assertSame(52, $deck->count());
    }

    public function testAddCardReturnsFluentInterface(): void
    {
        $deck = new Deck();
        $card = Card::fromString('As');
        $deck->removeCard($card);

        $result = $deck->addCard($card);

        $this->assertSame($deck, $result);
    }

    public function testAddCardThrowsExceptionForDuplicateCard(): void
    {
        $this->expectException(DuplicateCardException::class);

        $deck = new Deck();
        $card = Card::fromString('As');

        $deck->addCard($card, throwOnDuplicate: true);
    }

    public function testAddCardDoesNotThrowWhenFlagIsFalse(): void
    {
        $deck = new Deck();
        $card = Card::fromString('As');

        $deck->addCard($card, throwOnDuplicate: false);

        // Should still be 52 (card not added twice)
        $this->assertSame(52, $deck->count());
    }

    public function testAddCardsAddsMultipleCards(): void
    {
        $deck = new Deck();
        $cardsToRemove = [
            Card::fromString('As'),
            Card::fromString('Kh'),
        ];

        $deck->removeCards($cardsToRemove);
        $this->assertSame(50, $deck->count());

        $deck->addCards($cardsToRemove);
        $this->assertSame(52, $deck->count());
    }

    public function testIsEmptyReturnsTrueWhenDeckIsEmpty(): void
    {
        $deck = new Deck();
        $deck->draw(52);

        $this->assertTrue($deck->isEmpty());
    }

    public function testIsEmptyReturnsFalseWhenDeckHasCards(): void
    {
        $deck = new Deck();

        $this->assertFalse($deck->isEmpty());

        $deck->draw(51);

        $this->assertFalse($deck->isEmpty());
    }

    public function testGetRemainingCardsReturnsAllCards(): void
    {
        $deck = new Deck();
        $remaining = $deck->getRemainingCards();

        $this->assertCount(52, $remaining);

        foreach ($remaining as $card) {
            $this->assertInstanceOf(Card::class, $card);
        }
    }

    public function testGetRemainingCardsAfterDrawing(): void
    {
        $deck = new Deck();
        $deck->draw(10);

        $remaining = $deck->getRemainingCards();

        $this->assertCount(42, $remaining);
    }

    public function testFromStringArrayCreatesCustomDeck(): void
    {
        $cardStrings = ['As', 'Kh', 'Qd', '10c', '2s'];
        $deck = Deck::fromStringArray($cardStrings);

        $this->assertSame(5, $deck->count());
    }

    public function testFromStringArrayWithDuplicateThrowsException(): void
    {
        $this->expectException(DuplicateCardException::class);

        $cardStrings = ['As', 'Kh', 'As']; // Duplicate As
        Deck::fromStringArray($cardStrings, throwOnDuplicate: true);
    }

    public function testFromStringArrayWithDuplicateIgnoresWhenFlagIsFalse(): void
    {
        $cardStrings = ['As', 'Kh', 'As']; // Duplicate As
        $deck = Deck::fromStringArray($cardStrings, throwOnDuplicate: false);

        // Should only have 2 cards (As added once)
        $this->assertSame(2, $deck->count());
    }

    public function testMethodChainingWorks(): void
    {
        $deck = new Deck();

        $deck->shuffle()
            ->burn(3)
            ->draw(5);

        $this->assertSame(44, $deck->count());
    }

    public function testDeckContainsAllRanksAndSuits(): void
    {
        $deck = new Deck();
        $cards = $deck->getRemainingCards();

        $cardNotations = array_map(fn (Card $card) => $card->toString(), $cards);

        // Check we have 13 ranks × 4 suits = 52 unique cards
        $this->assertCount(52, array_unique($cardNotations));

        // Check specific cards exist
        $this->assertContains('As', $cardNotations);
        $this->assertContains('Kh', $cardNotations);
        $this->assertContains('2c', $cardNotations);
        $this->assertContains('10d', $cardNotations);
    }
}
