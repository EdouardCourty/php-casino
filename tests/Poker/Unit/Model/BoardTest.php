<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Tests\Poker\Unit\Model;

use Ecourty\PHPCasino\Poker\Enum\CardRank;
use Ecourty\PHPCasino\Poker\Enum\CardSuit;
use Ecourty\PHPCasino\Poker\Enum\Street;
use Ecourty\PHPCasino\Poker\Exception\DuplicateCardException;
use Ecourty\PHPCasino\Poker\Exception\InvalidBoardStateException;
use Ecourty\PHPCasino\Poker\Exception\InvalidCardCountException;
use Ecourty\PHPCasino\Poker\Exception\NotEnoughCardsException;
use Ecourty\PHPCasino\Poker\Model\Board;
use Ecourty\PHPCasino\Poker\Model\Card;
use Ecourty\PHPCasino\Poker\Model\Deck;
use PHPUnit\Framework\TestCase;

class BoardTest extends TestCase
{
    /**
     * Helper to prepare a deck for a given street with community cards.
     * Removes community cards and burns the appropriate number of cards.
     *
     * @param array<Card> $communityCards
     */
    private function prepareDeckForStreet(Street $street, array $communityCards): Deck
    {
        $deck = new Deck();
        $deck->removeCards($communityCards);
        $deck->burn($street->getTotalBurnsBeforeStreet());
        return $deck;
    }

    public function testConstructorAtPreflopWithNoDeck(): void
    {
        $board = new Board(Street::PREFLOP);

        $this->assertSame(Street::PREFLOP, $board->getCurrentStreet());
        $this->assertCount(0, $board->getCommunityCards());
        $this->assertSame(52, $board->getRemainingDeckCount());
    }

    public function testConstructorAtPreflopWithDeck(): void
    {
        $deck = new Deck();
        $board = new Board(Street::PREFLOP, [], $deck);

        $this->assertSame(Street::PREFLOP, $board->getCurrentStreet());
        $this->assertCount(0, $board->getCommunityCards());
        $this->assertSame(52, $board->getRemainingDeckCount());
    }

    public function testConstructorAtFlopWithValidCards(): void
    {
        $cards = [
            new Card(CardRank::ACE, CardSuit::HEARTS),
            new Card(CardRank::KING, CardSuit::DIAMONDS),
            new Card(CardRank::QUEEN, CardSuit::SPADES),
        ];
        $deck = $this->prepareDeckForStreet(Street::FLOP, $cards);

        $board = new Board(Street::FLOP, $cards, $deck);

        $this->assertSame(Street::FLOP, $board->getCurrentStreet());
        $this->assertCount(3, $board->getCommunityCards());
        $this->assertSame(48, $board->getRemainingDeckCount());
    }

    public function testConstructorThrowsOnInvalidCardCount(): void
    {
        $this->expectException(InvalidCardCountException::class);
        $this->expectExceptionMessage('Invalid card count for street flop: expected 3 cards, got 2');

        $cards = [
            new Card(CardRank::ACE, CardSuit::HEARTS),
            new Card(CardRank::KING, CardSuit::DIAMONDS),
        ];
        $deck = new Deck();
        $deck->removeCards($cards);

        new Board(Street::FLOP, $cards, $deck);
    }

    public function testConstructorThrowsOnDuplicateCards(): void
    {
        $this->expectException(DuplicateCardException::class);

        $cards = [
            new Card(CardRank::ACE, CardSuit::HEARTS),
            new Card(CardRank::KING, CardSuit::DIAMONDS),
            new Card(CardRank::ACE, CardSuit::HEARTS),
        ];

        new Board(Street::FLOP, $cards, new Deck());
    }

    public function testConstructorThrowsWhenDeckContainsCommunityCards(): void
    {
        $this->expectException(DuplicateCardException::class);

        $cards = [
            new Card(CardRank::ACE, CardSuit::HEARTS),
            new Card(CardRank::KING, CardSuit::DIAMONDS),
            new Card(CardRank::QUEEN, CardSuit::SPADES),
        ];
        $deck = new Deck(); // Deck still contains these cards

        new Board(Street::FLOP, $cards, $deck);
    }

    public function testConstructorValidatesDeckBurnCountAtFlop(): void
    {
        $cards = [
            new Card(CardRank::ACE, CardSuit::HEARTS),
            new Card(CardRank::KING, CardSuit::DIAMONDS),
            new Card(CardRank::QUEEN, CardSuit::SPADES),
        ];
        $deck = $this->prepareDeckForStreet(Street::FLOP, $cards);

        // Should work: deck(48) + community(3) + burns(1) = 52
        $board = Board::createAtFlop($cards, $deck);
        $this->assertSame(48, $board->getRemainingDeckCount());
    }

    public function testConstructorThrowsWhenDeckNotBurnedCorrectlyAtFlop(): void
    {
        $this->expectException(InvalidBoardStateException::class);
        $this->expectExceptionMessage('Invalid deck state for street flop');

        $cards = [
            new Card(CardRank::ACE, CardSuit::HEARTS),
            new Card(CardRank::KING, CardSuit::DIAMONDS),
            new Card(CardRank::QUEEN, CardSuit::SPADES),
        ];
        $deck = new Deck();
        $deck->removeCards($cards);
        // Missing burn: deck(49) + community(3) + burns(0) = 52 ❌

        Board::createAtFlop($cards, $deck);
    }

    public function testConstructorValidatesDeckBurnCountAtTurn(): void
    {
        $cards = [
            new Card(CardRank::ACE, CardSuit::HEARTS),
            new Card(CardRank::KING, CardSuit::DIAMONDS),
            new Card(CardRank::QUEEN, CardSuit::SPADES),
            new Card(CardRank::JACK, CardSuit::CLUBS),
        ];
        $deck = $this->prepareDeckForStreet(Street::TURN, $cards);

        // Should work: deck(46) + community(4) + burns(2) = 52
        $board = Board::createAtTurn($cards, $deck);
        $this->assertSame(46, $board->getRemainingDeckCount());
    }

    public function testConstructorThrowsWhenDeckNotBurnedCorrectlyAtTurn(): void
    {
        $this->expectException(InvalidBoardStateException::class);
        $this->expectExceptionMessage('Invalid deck state for street turn');

        $cards = [
            new Card(CardRank::ACE, CardSuit::HEARTS),
            new Card(CardRank::KING, CardSuit::DIAMONDS),
            new Card(CardRank::QUEEN, CardSuit::SPADES),
            new Card(CardRank::JACK, CardSuit::CLUBS),
        ];
        $deck = new Deck();
        $deck->removeCards($cards);
        $deck->burn(1); // Wrong: only 1 burn, should be 2

        Board::createAtTurn($cards, $deck);
    }

    public function testConstructorValidatesDeckBurnCountAtRiver(): void
    {
        $cards = [
            new Card(CardRank::ACE, CardSuit::HEARTS),
            new Card(CardRank::KING, CardSuit::DIAMONDS),
            new Card(CardRank::QUEEN, CardSuit::SPADES),
            new Card(CardRank::JACK, CardSuit::CLUBS),
            new Card(CardRank::TEN, CardSuit::HEARTS),
        ];
        $deck = $this->prepareDeckForStreet(Street::RIVER, $cards);

        // Should work: deck(44) + community(5) + burns(3) = 52
        $board = Board::createAtRiver($cards, $deck);
        $this->assertSame(44, $board->getRemainingDeckCount());
    }

    public function testConstructorThrowsWhenDeckNotBurnedCorrectlyAtRiver(): void
    {
        $this->expectException(InvalidBoardStateException::class);
        $this->expectExceptionMessage('Invalid deck state for street river');

        $cards = [
            new Card(CardRank::ACE, CardSuit::HEARTS),
            new Card(CardRank::KING, CardSuit::DIAMONDS),
            new Card(CardRank::QUEEN, CardSuit::SPADES),
            new Card(CardRank::JACK, CardSuit::CLUBS),
            new Card(CardRank::TEN, CardSuit::HEARTS),
        ];
        $deck = new Deck();
        $deck->removeCards($cards);
        // Missing burns: deck(47) + community(5) + burns(0) = 52 ❌

        Board::createAtRiver($cards, $deck);
    }

    public function testCreateAtPreflop(): void
    {
        $board = Board::createAtPreflop();

        $this->assertSame(Street::PREFLOP, $board->getCurrentStreet());
        $this->assertCount(0, $board->getCommunityCards());
        $this->assertSame(52, $board->getRemainingDeckCount());
    }

    public function testCreateAtPreflopWithDeck(): void
    {
        $deck = new Deck();
        $board = Board::createAtPreflop($deck);

        $this->assertSame(Street::PREFLOP, $board->getCurrentStreet());
        $this->assertSame(52, $board->getRemainingDeckCount());
    }

    public function testCreateAtFlop(): void
    {
        $cards = [
            new Card(CardRank::ACE, CardSuit::HEARTS),
            new Card(CardRank::KING, CardSuit::DIAMONDS),
            new Card(CardRank::QUEEN, CardSuit::SPADES),
        ];
        $deck = $this->prepareDeckForStreet(Street::FLOP, $cards);

        $board = Board::createAtFlop($cards, $deck);

        $this->assertSame(Street::FLOP, $board->getCurrentStreet());
        $this->assertCount(3, $board->getCommunityCards());
    }

    public function testCreateAtTurn(): void
    {
        $cards = [
            new Card(CardRank::ACE, CardSuit::HEARTS),
            new Card(CardRank::KING, CardSuit::DIAMONDS),
            new Card(CardRank::QUEEN, CardSuit::SPADES),
            new Card(CardRank::JACK, CardSuit::CLUBS),
        ];
        $deck = $this->prepareDeckForStreet(Street::TURN, $cards);

        $board = Board::createAtTurn($cards, $deck);

        $this->assertSame(Street::TURN, $board->getCurrentStreet());
        $this->assertCount(4, $board->getCommunityCards());
    }

    public function testCreateAtRiver(): void
    {
        $cards = [
            new Card(CardRank::ACE, CardSuit::HEARTS),
            new Card(CardRank::KING, CardSuit::DIAMONDS),
            new Card(CardRank::QUEEN, CardSuit::SPADES),
            new Card(CardRank::JACK, CardSuit::CLUBS),
            new Card(CardRank::TEN, CardSuit::HEARTS),
        ];
        $deck = $this->prepareDeckForStreet(Street::RIVER, $cards);

        $board = Board::createAtRiver($cards, $deck);

        $this->assertSame(Street::RIVER, $board->getCurrentStreet());
        $this->assertCount(5, $board->getCommunityCards());
    }

    public function testFromString(): void
    {
        $cards = [
            Card::fromString('Ah'),
            Card::fromString('Kd'),
            Card::fromString('Qs'),
        ];
        $deck = $this->prepareDeckForStreet(Street::FLOP, $cards);

        $board = Board::fromString(Street::FLOP, 'Ah Kd Qs', $deck);

        $this->assertSame(Street::FLOP, $board->getCurrentStreet());
        $this->assertCount(3, $board->getCommunityCards());
    }

    public function testFromStringWithCommas(): void
    {
        $cards = [
            Card::fromString('Ah'),
            Card::fromString('Kd'),
            Card::fromString('Qs'),
        ];
        $deck = $this->prepareDeckForStreet(Street::FLOP, $cards);

        $board = Board::fromString(Street::FLOP, 'Ah,Kd,Qs', $deck);

        $this->assertCount(3, $board->getCommunityCards());
    }

    public function testAdvanceToNextStreetFromPreflop(): void
    {
        $board = Board::createAtPreflop();
        $initialDeckCount = $board->getRemainingDeckCount();

        $board->advanceToNextStreet();

        $this->assertSame(Street::FLOP, $board->getCurrentStreet());
        $this->assertCount(3, $board->getCommunityCards());
        // Should burn 1 and draw 3 = 4 cards removed
        $this->assertSame($initialDeckCount - 4, $board->getRemainingDeckCount());
    }

    public function testAdvanceToNextStreetFromFlop(): void
    {
        $board = Board::createAtPreflop();
        $board->advanceToNextStreet(); // To flop
        $deckCountAtFlop = $board->getRemainingDeckCount();

        $board->advanceToNextStreet(); // To turn

        $this->assertSame(Street::TURN, $board->getCurrentStreet());
        $this->assertCount(4, $board->getCommunityCards());
        // Should burn 1 and draw 1 = 2 cards removed
        $this->assertSame($deckCountAtFlop - 2, $board->getRemainingDeckCount());
    }

    public function testAdvanceToNextStreetFromTurn(): void
    {
        $board = Board::createAtPreflop();
        $board->advanceToNextStreet(); // To flop
        $board->advanceToNextStreet(); // To turn
        $deckCountAtTurn = $board->getRemainingDeckCount();

        $board->advanceToNextStreet(); // To river

        $this->assertSame(Street::RIVER, $board->getCurrentStreet());
        $this->assertCount(5, $board->getCommunityCards());
        // Should burn 1 and draw 1 = 2 cards removed
        $this->assertSame($deckCountAtTurn - 2, $board->getRemainingDeckCount());
    }

    public function testAdvanceToNextStreetFromRiver(): void
    {
        $board = Board::createAtPreflop();
        $board->advanceToNextStreet(); // To flop
        $board->advanceToNextStreet(); // To turn
        $board->advanceToNextStreet(); // To river
        $deckCountAtRiver = $board->getRemainingDeckCount();

        $board->advanceToNextStreet(); // To showdown

        $this->assertSame(Street::SHOWDOWN, $board->getCurrentStreet());
        $this->assertCount(5, $board->getCommunityCards());
        // Showdown doesn't burn or draw
        $this->assertSame($deckCountAtRiver, $board->getRemainingDeckCount());
    }

    public function testAdvanceToNextStreetThrowsAtShowdown(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot advance past showdown');

        $board = Board::createAtPreflop();
        $board->advanceToNextStreet(); // To flop
        $board->advanceToNextStreet(); // To turn
        $board->advanceToNextStreet(); // To river
        $board->advanceToNextStreet(); // To showdown
        $board->advanceToNextStreet(); // Should throw
    }

    public function testAdvanceToNextStreetThrowsWhenNotEnoughCards(): void
    {
        $this->expectException(NotEnoughCardsException::class);

        $deck = new Deck();
        // Draw all but 2 cards
        $deck->draw(50);

        $board = new Board(Street::PREFLOP, [], $deck);
        $board->advanceToNextStreet(); // Needs 4 cards (1 burn + 3 draw), but only 2 available
    }

    public function testReset(): void
    {
        $board = Board::createAtPreflop();
        $board->advanceToNextStreet(); // To flop
        $board->advanceToNextStreet(); // To turn

        $board->reset();

        $this->assertSame(Street::PREFLOP, $board->getCurrentStreet());
        $this->assertCount(0, $board->getCommunityCards());
        $this->assertSame(52, $board->getRemainingDeckCount());
    }

    public function testResetWithProvidedDeck(): void
    {
        $board = Board::createAtPreflop();
        $board->advanceToNextStreet();

        $newDeck = new Deck();
        $board->reset($newDeck);

        $this->assertSame(Street::PREFLOP, $board->getCurrentStreet());
        $this->assertCount(0, $board->getCommunityCards());
        $this->assertSame(52, $board->getRemainingDeckCount());
    }

    public function testIsSuitedReturnsTrueForMonotoneBoard(): void
    {
        $cards = [
            new Card(CardRank::ACE, CardSuit::HEARTS),
            new Card(CardRank::KING, CardSuit::HEARTS),
            new Card(CardRank::QUEEN, CardSuit::HEARTS),
        ];
        $deck = $this->prepareDeckForStreet(Street::FLOP, $cards);

        $board = Board::createAtFlop($cards, $deck);

        $this->assertTrue($board->isSuited());
    }

    public function testIsSuitedReturnsFalseForNonMonotoneBoard(): void
    {
        $cards = [
            new Card(CardRank::ACE, CardSuit::HEARTS),
            new Card(CardRank::KING, CardSuit::DIAMONDS),
            new Card(CardRank::QUEEN, CardSuit::HEARTS),
        ];
        $deck = $this->prepareDeckForStreet(Street::FLOP, $cards);

        $board = Board::createAtFlop($cards, $deck);

        $this->assertFalse($board->isSuited());
    }

    public function testIsSuitedReturnsFalseForLessThanTwoCards(): void
    {
        $board = Board::createAtPreflop();

        $this->assertFalse($board->isSuited());
    }

    public function testIsMonotoneIsAliasForIsSuited(): void
    {
        $cards = [
            new Card(CardRank::ACE, CardSuit::HEARTS),
            new Card(CardRank::KING, CardSuit::HEARTS),
            new Card(CardRank::QUEEN, CardSuit::HEARTS),
        ];
        $deck = $this->prepareDeckForStreet(Street::FLOP, $cards);

        $board = Board::createAtFlop($cards, $deck);

        $this->assertSame($board->isSuited(), $board->isMonotone());
    }

    public function testIsRainbowReturnsTrueForThreeDifferentSuits(): void
    {
        $cards = [
            new Card(CardRank::ACE, CardSuit::HEARTS),
            new Card(CardRank::KING, CardSuit::DIAMONDS),
            new Card(CardRank::QUEEN, CardSuit::SPADES),
        ];
        $deck = $this->prepareDeckForStreet(Street::FLOP, $cards);

        $board = Board::createAtFlop($cards, $deck);

        $this->assertTrue($board->isRainbow());
    }

    public function testIsRainbowReturnsFalseForTwoSameSuits(): void
    {
        $cards = [
            new Card(CardRank::ACE, CardSuit::HEARTS),
            new Card(CardRank::KING, CardSuit::HEARTS),
            new Card(CardRank::QUEEN, CardSuit::SPADES),
        ];
        $deck = $this->prepareDeckForStreet(Street::FLOP, $cards);

        $board = Board::createAtFlop($cards, $deck);

        $this->assertFalse($board->isRainbow());
    }

    public function testIsRainbowReturnsFalseForNonFlopBoards(): void
    {
        $board = Board::createAtPreflop();

        $this->assertFalse($board->isRainbow());
    }

    public function testIsPairedReturnsTrueWhenPairExists(): void
    {
        $cards = [
            new Card(CardRank::ACE, CardSuit::HEARTS),
            new Card(CardRank::ACE, CardSuit::DIAMONDS),
            new Card(CardRank::QUEEN, CardSuit::SPADES),
        ];
        $deck = $this->prepareDeckForStreet(Street::FLOP, $cards);

        $board = Board::createAtFlop($cards, $deck);

        $this->assertTrue($board->isPaired());
    }

    public function testIsPairedReturnsFalseWhenNoPair(): void
    {
        $cards = [
            new Card(CardRank::ACE, CardSuit::HEARTS),
            new Card(CardRank::KING, CardSuit::DIAMONDS),
            new Card(CardRank::QUEEN, CardSuit::SPADES),
        ];
        $deck = $this->prepareDeckForStreet(Street::FLOP, $cards);

        $board = Board::createAtFlop($cards, $deck);

        $this->assertFalse($board->isPaired());
    }

    public function testHasFlushDrawReturnsTrueWithThreeOfSameSuit(): void
    {
        $cards = [
            new Card(CardRank::ACE, CardSuit::HEARTS),
            new Card(CardRank::KING, CardSuit::HEARTS),
            new Card(CardRank::QUEEN, CardSuit::HEARTS),
        ];
        $deck = $this->prepareDeckForStreet(Street::FLOP, $cards);

        $board = Board::createAtFlop($cards, $deck);

        $this->assertTrue($board->hasFlushDraw());
    }

    public function testHasFlushDrawReturnsFalseWithoutThreeOfSameSuit(): void
    {
        $cards = [
            new Card(CardRank::ACE, CardSuit::HEARTS),
            new Card(CardRank::KING, CardSuit::HEARTS),
            new Card(CardRank::QUEEN, CardSuit::SPADES),
        ];
        $deck = $this->prepareDeckForStreet(Street::FLOP, $cards);

        $board = Board::createAtFlop($cards, $deck);

        $this->assertFalse($board->hasFlushDraw());
    }

    public function testHasStraightDrawReturnsTrueWhenPossible(): void
    {
        $cards = [
            new Card(CardRank::NINE, CardSuit::HEARTS),
            new Card(CardRank::TEN, CardSuit::DIAMONDS),
            new Card(CardRank::JACK, CardSuit::SPADES),
        ];
        $deck = $this->prepareDeckForStreet(Street::FLOP, $cards);

        $board = Board::createAtFlop($cards, $deck);

        $this->assertTrue($board->hasStraightDraw());
    }

    public function testHasStraightDrawReturnsFalseWhenNotPossible(): void
    {
        $cards = [
            new Card(CardRank::TWO, CardSuit::HEARTS),
            new Card(CardRank::SEVEN, CardSuit::DIAMONDS),
            new Card(CardRank::KING, CardSuit::SPADES),
        ];
        $deck = $this->prepareDeckForStreet(Street::FLOP, $cards);

        $board = Board::createAtFlop($cards, $deck);

        $this->assertFalse($board->hasStraightDraw());
    }

    public function testGetSuitDistribution(): void
    {
        $cards = [
            new Card(CardRank::ACE, CardSuit::HEARTS),
            new Card(CardRank::KING, CardSuit::HEARTS),
            new Card(CardRank::QUEEN, CardSuit::SPADES),
        ];
        $deck = $this->prepareDeckForStreet(Street::FLOP, $cards);

        $board = Board::createAtFlop($cards, $deck);
        $distribution = $board->getSuitDistribution();

        $this->assertSame(2, $distribution['h']);
        $this->assertSame(1, $distribution['s']);
        $this->assertArrayNotHasKey('d', $distribution);
        $this->assertArrayNotHasKey('c', $distribution);
    }

    public function testGetRankDistribution(): void
    {
        $cards = [
            new Card(CardRank::ACE, CardSuit::HEARTS),
            new Card(CardRank::ACE, CardSuit::DIAMONDS),
            new Card(CardRank::QUEEN, CardSuit::SPADES),
        ];
        $deck = $this->prepareDeckForStreet(Street::FLOP, $cards);

        $board = Board::createAtFlop($cards, $deck);
        $distribution = $board->getRankDistribution();

        $this->assertSame(2, $distribution['A']);
        $this->assertSame(1, $distribution['Q']);
    }

    public function testGetCommunityCardsReturnsCorrectCards(): void
    {
        $cards = [
            new Card(CardRank::ACE, CardSuit::HEARTS),
            new Card(CardRank::KING, CardSuit::DIAMONDS),
            new Card(CardRank::QUEEN, CardSuit::SPADES),
        ];
        $deck = $this->prepareDeckForStreet(Street::FLOP, $cards);

        $board = Board::createAtFlop($cards, $deck);
        $communityCards = $board->getCommunityCards();

        $this->assertCount(3, $communityCards);
        $this->assertTrue($communityCards[0]->equals($cards[0]));
        $this->assertTrue($communityCards[1]->equals($cards[1]));
        $this->assertTrue($communityCards[2]->equals($cards[2]));
    }
}
