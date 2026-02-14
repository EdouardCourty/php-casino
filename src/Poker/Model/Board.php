<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Poker\Model;

use Ecourty\PHPCasino\Poker\Enum\Street;
use Ecourty\PHPCasino\Poker\Exception\DuplicateCardException;
use Ecourty\PHPCasino\Poker\Exception\InvalidBoardStateException;
use Ecourty\PHPCasino\Poker\Exception\InvalidCardCountException;
use Ecourty\PHPCasino\Poker\Exception\NotEnoughCardsException;

/**
 * Represents a poker board with community cards and current street.
 * Manages automatic card dealing from a deck with burning rules.
 */
final class Board
{
    /**
     * @var array<Card> Community cards on the board
     */
    private array $communityCards;

    /**
     * @param Street $currentStreet Current street (preflop, flop, turn, river, showdown)
     * @param array<Card> $communityCards Community cards on the board
     * @param Deck|null $deck Optional deck for automatic dealing (creates new shuffled deck if null at preflop)
     *
     * @throws InvalidCardCountException If card count doesn't match street requirement
     * @throws DuplicateCardException If duplicate cards found in community cards or deck contains community cards
     */
    public function __construct(
        private Street $currentStreet,
        array $communityCards = [],
        private ?Deck $deck = null,
    ) {
        $this->communityCards = array_values($communityCards);

        // Validate card count matches street
        $expectedCount = $this->currentStreet->getCommunityCardsCount();
        if (count($this->communityCards) !== $expectedCount) {
            throw InvalidCardCountException::forStreet($this->currentStreet, $expectedCount, count($this->communityCards));
        }

        // Check for duplicates in community cards
        $this->validateNoDuplicates($this->communityCards);

        // Create new shuffled deck if none provided and at preflop
        if ($this->deck === null && $this->currentStreet === Street::PREFLOP) {
            $this->deck = Deck::shuffled();
        }

        // If community cards provided, validate deck state
        if (!empty($this->communityCards) && $this->deck !== null) {
            $this->validateDeckDoesNotContainCommunityCards();
            $this->validateDeckBurnCount();
        }
    }

    /**
     * Creates a new board at preflop with no community cards.
     * If no deck provided, creates and shuffles a new one.
     */
    public static function createAtPreflop(?Deck $deck = null): self
    {
        return new self(Street::PREFLOP, [], $deck);
    }

    /**
     * Creates a new board at flop with 3 community cards.
     * Deck must be provided and must not contain the community cards.
     *
     * @param array<Card> $cards Exactly 3 cards
     *
     * @throws InvalidCardCountException If not exactly 3 cards provided
     */
    public static function createAtFlop(array $cards, Deck $deck): self
    {
        return new self(Street::FLOP, $cards, $deck);
    }

    /**
     * Creates a new board at turn with 4 community cards.
     * Deck must be provided and must not contain the community cards.
     *
     * @param array<Card> $cards Exactly 4 cards
     *
     * @throws InvalidCardCountException If not exactly 4 cards provided
     */
    public static function createAtTurn(array $cards, Deck $deck): self
    {
        return new self(Street::TURN, $cards, $deck);
    }

    /**
     * Creates a new board at river with 5 community cards.
     * Deck must be provided and must not contain the community cards.
     *
     * @param array<Card> $cards Exactly 5 cards
     *
     * @throws InvalidCardCountException If not exactly 5 cards provided
     */
    public static function createAtRiver(array $cards, Deck $deck): self
    {
        return new self(Street::RIVER, $cards, $deck);
    }

    /**
     * Creates a board from string notation.
     *
     * @param Street $street The street to create the board at
     * @param string $cardsString Space or comma-separated card notation (e.g., "AhKdQs" or "Ah Kd Qs")
     * @param Deck $deck Deck to use (required if cards provided)
     *
     * @throws InvalidCardCountException If card count doesn't match street
     */
    public static function fromString(Street $street, string $cardsString, Deck $deck): self
    {
        $cardsString = trim($cardsString);
        if ($cardsString === '') {
            return new self($street, [], $deck);
        }

        // Split by space or comma
        $cardStrings = preg_split('/[\s,]+/', $cardsString);
        if ($cardStrings === false) {
            return new self($street, [], $deck);
        }
        $cards = array_map(fn(string $str) => Card::fromString($str), $cardStrings);

        return new self($street, $cards, $deck);
    }

    /**
     * Returns the current street.
     */
    public function getCurrentStreet(): Street
    {
        return $this->currentStreet;
    }

    /**
     * Returns all community cards on the board.
     *
     * @return array<Card>
     */
    public function getCommunityCards(): array
    {
        return $this->communityCards;
    }

    /**
     * Returns the number of community cards on the board.
     */
    public function getCardCount(): int
    {
        return count($this->communityCards);
    }

    /**
     * Returns the number of cards remaining in the deck.
     */
    public function getRemainingDeckCount(): int
    {
        return $this->deck?->count() ?? 0;
    }

    /**
     * Advances to the next street, automatically burning and drawing cards from the deck.
     * Burns 1 card before dealing according to poker rules (flop/turn/river).
     * Returns $this for method chaining.
     *
     * @throws NotEnoughCardsException If deck doesn't have enough cards
     * @throws \RuntimeException If already at showdown or no deck available
     */
    public function advanceToNextStreet(): self
    {
        $nextStreet = $this->currentStreet->getNextStreet();
        if ($nextStreet === null) {
            throw new \RuntimeException('Cannot advance past showdown');
        }

        if ($this->deck === null) {
            throw new \RuntimeException('Cannot advance street without a deck');
        }

        // Determine cards to burn and draw based on next street
        $cardsToBurn = $nextStreet->isPostflop() && $nextStreet !== Street::SHOWDOWN ? 1 : 0;
        $cardsToDraw = $nextStreet->getCardsToDeal();

        // Validate deck has enough cards
        $totalCardsNeeded = $cardsToBurn + $cardsToDraw;
        if ($this->deck->count() < $totalCardsNeeded) {
            throw NotEnoughCardsException::forDraw($totalCardsNeeded, $this->deck->count());
        }

        // Burn card(s) if needed
        if ($cardsToBurn > 0) {
            $this->deck->burn($cardsToBurn);
        }

        // Draw new community cards if needed
        if ($cardsToDraw > 0) {
            $newCards = $this->deck->draw($cardsToDraw);
            $this->communityCards = array_merge($this->communityCards, $newCards);
        }

        // Update street
        $this->currentStreet = $nextStreet;

        return $this;
    }

    /**
     * Resets the board to preflop with no community cards.
     * Uses provided deck or creates a new shuffled one.
     * Returns $this for method chaining.
     */
    public function reset(?Deck $deck = null): self
    {
        $this->currentStreet = Street::PREFLOP;
        $this->communityCards = [];
        $this->deck = $deck ?? Deck::shuffled();

        return $this;
    }

    /**
     * Checks if all community cards are the same suit (monotone/suited board).
     * Returns false if less than 2 cards.
     */
    public function isSuited(): bool
    {
        if (count($this->communityCards) < 2) {
            return false;
        }

        $firstSuit = $this->communityCards[0]->suit;
        foreach ($this->communityCards as $card) {
            if ($card->suit !== $firstSuit) {
                return false;
            }
        }

        return true;
    }

    /**
     * Alias for isSuited(). Checks if all community cards are the same suit.
     */
    public function isMonotone(): bool
    {
        return $this->isSuited();
    }

    /**
     * Checks if all community cards have different suits (rainbow board).
     * Only meaningful for flop (3 cards). Returns false otherwise.
     */
    public function isRainbow(): bool
    {
        if (count($this->communityCards) !== 3) {
            return false;
        }

        $suits = array_map(fn(Card $card) => $card->suit->value, $this->communityCards);
        return count(array_unique($suits)) === 3;
    }

    /**
     * Checks if at least one pair exists on the board (any rank appears 2+ times).
     */
    public function isPaired(): bool
    {
        $rankCounts = $this->getRankDistribution();
        foreach ($rankCounts as $count) {
            if ($count >= 2) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the board has a flush draw (3 or more cards of the same suit).
     */
    public function hasFlushDraw(): bool
    {
        $suitCounts = $this->getSuitDistribution();
        foreach ($suitCounts as $count) {
            if ($count >= 3) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the board has straight draw possibilities.
     * A straight draw exists if there are cards that allow for straight possibilities.
     * This checks if the range between the highest and lowest card is <= 4.
     */
    public function hasStraightDraw(): bool
    {
        if (count($this->communityCards) < 3) {
            return false;
        }

        $values = array_map(fn(Card $card) => $card->getValue(), $this->communityCards);
        sort($values);

        // Check if the spread between min and max is <= 4 (allows for a straight)
        $spread = $values[count($values) - 1] - $values[0];

        return $spread <= 4;
    }

    /**
     * Returns the distribution of suits on the board.
     *
     * @return array<string, int> Suit value => count
     */
    public function getSuitDistribution(): array
    {
        $distribution = [];
        foreach ($this->communityCards as $card) {
            $suitValue = $card->suit->value;
            $distribution[$suitValue] = ($distribution[$suitValue] ?? 0) + 1;
        }

        return $distribution;
    }

    /**
     * Returns the distribution of ranks on the board.
     *
     * @return array<string, int<1, max>> Rank value => count
     */
    public function getRankDistribution(): array
    {
        $distribution = [];
        foreach ($this->communityCards as $card) {
            $rankValue = $card->rank->value;
            $distribution[$rankValue] = ($distribution[$rankValue] ?? 0) + 1;
        }

        /** @var array<string, int<1, max>> */
        return $distribution;
    }

    /**
     * Validates that there are no duplicate cards in the provided array.
     *
     * @param array<Card> $cards
     *
     * @throws DuplicateCardException
     */
    private function validateNoDuplicates(array $cards): void
    {
        $seen = [];
        $duplicates = [];

        foreach ($cards as $i => $card) {
            // Check if this card already exists in seen cards
            foreach ($seen as $seenCard) {
                if ($card->equals($seenCard)) {
                    $duplicates[] = $card->toString();
                    break;
                }
            }
            $seen[] = $card;
        }

        if (!empty($duplicates)) {
            throw DuplicateCardException::fromCards($duplicates);
        }
    }

    /**
     * Validates that the deck doesn't contain any of the community cards.
     *
     * @throws DuplicateCardException If deck contains community cards
     */
    private function validateDeckDoesNotContainCommunityCards(): void
    {
        if ($this->deck === null) {
            return;
        }

        $deckCards = $this->deck->getRemainingCards();
        $communityCardStrings = array_map(fn(Card $card) => $card->toString(), $this->communityCards);
        $duplicates = [];

        foreach ($deckCards as $deckCard) {
            $deckCardString = $deckCard->toString();
            if (in_array($deckCardString, $communityCardStrings, true)) {
                $duplicates[] = $deckCardString;
            }
        }

        if (!empty($duplicates)) {
            throw DuplicateCardException::fromCards($duplicates);
        }
    }

    /**
     * Validates that the deck has been properly burned for the current street.
     * Formula: deck count + community cards count + burns = 52
     *
     * @throws InvalidBoardStateException If deck burn count is incorrect
     */
    private function validateDeckBurnCount(): void
    {
        if ($this->deck === null) {
            return;
        }

        $deckCount = $this->deck->count();
        $communityCount = count($this->communityCards);
        $expectedBurns = $this->currentStreet->getTotalBurnsBeforeStreet();
        
        $actualTotal = $deckCount + $communityCount + $expectedBurns;
        
        if ($actualTotal !== Deck::MAX_COUNT) {
            throw InvalidBoardStateException::invalidDeckBurnCount(
                $this->currentStreet,
                Deck::MAX_COUNT,
                $actualTotal
            );
        }
    }
}
