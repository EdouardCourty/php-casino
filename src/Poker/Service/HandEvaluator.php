<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Poker\Service;

use Ecourty\PHPCasino\Poker\Enum\CardRank;
use Ecourty\PHPCasino\Poker\Enum\CardSuit;
use Ecourty\PHPCasino\Poker\Enum\HandRank;
use Ecourty\PHPCasino\Poker\Exception\InvalidCardNotationException;
use Ecourty\PHPCasino\Poker\Exception\InvalidRankException;
use Ecourty\PHPCasino\Poker\Exception\InvalidSuitException;
use Ecourty\PHPCasino\Poker\Exception\NoValidHandFoundException;
use Ecourty\PHPCasino\Poker\Model\Card;
use Ecourty\PHPCasino\Poker\Model\Hand;

class HandEvaluator
{
    /**
     * Evaluates the best possible 5-card poker hand from hole cards and community cards.
     *
     * Combines the player's hole cards with community cards and tests all possible
     * 5-card combinations to find the strongest hand according to poker rules.
     *
     * @param array<string|Card> $holeCards Player's 2 hole cards (strings like 'Ah' or Card objects)
     * @param array<string|Card> $communityCards Community cards (0-5 cards, strings or Card objects)
     *
     * @throws NoValidHandFoundException If no valid 5-card hand can be formed (should never happen with 7 cards)
     * @throws InvalidCardNotationException If any card string is invalid (e.g., '1h', 'Zs')
     * @throws InvalidRankException If any card rank is invalid (e.g., '1', 'Z')
     * @throws InvalidSuitException If any card suit is invalid (e.g., 'h', 'x')
     *
     * @return Hand The best hand found with rank, cards, and kickers
     */
    public function evaluateBestHand(array $holeCards, array $communityCards): Hand
    {
        // Convert strings to Card objects if needed
        $holeCardsObjects = array_map(
            static fn (string|Card $card) => $card instanceof Card ? $card : Card::fromString($card),
            $holeCards,
        );
        $communityCardsObjects = array_map(
            static fn (string|Card $card) => $card instanceof Card ? $card : Card::fromString($card),
            $communityCards,
        );

        $allCards = array_merge($holeCardsObjects, $communityCardsObjects);

        $bestHand = null;
        $bestRank = null;
        $bestKickers = [];

        $combinations = $this->getCombinations($allCards, 5);

        foreach ($combinations as $hand) {
            [$rank, $kickers] = $this->evaluateHand($hand);

            if ($bestRank === null || $rank->value > $bestRank->value || ($rank->value === $bestRank->value && $this->compareKickerRanks($kickers, $bestKickers) > 0)) {
                $bestRank = $rank;
                $bestKickers = $kickers;
                $bestHand = $hand;
            }
        }

        if ($bestHand === null || $bestRank === null) {
            throw NoValidHandFoundException::fromCards($allCards);
        }

        return new Hand(
            rank: $bestRank,
            cards: $bestHand,
            kickers: $bestKickers,
        );
    }

    /**
     * Compares two poker hands to determine which is stronger.
     *
     * First compares hand ranks (straight flush > four of a kind > etc.).
     * If ranks are equal, compares kickers (highest cards) to break the tie.
     *
     * @param Hand $hand1 First hand to compare
     * @param Hand $hand2 Second hand to compare
     *
     * @return int Negative if hand1 < hand2, 0 if equal, positive if hand1 > hand2
     */
    public function compareHands(Hand $hand1, Hand $hand2): int
    {
        if ($hand1->rank->value !== $hand2->rank->value) {
            return $hand1->rank->value <=> $hand2->rank->value;
        }

        return $this->compareKickerRanks($hand1->kickers, $hand2->kickers);
    }

    /**
     * Evaluates a single 5-card hand and determines its rank and kickers.
     *
     * @param array<Card> $hand Exactly 5 Card objects
     *
     * @return array{0: HandRank, 1: array<CardRank>} Tuple of [hand rank, kicker ranks (sorted desc)]
     */
    private function evaluateHand(array $hand): array
    {
        // Extract CardRank enums from cards
        $cardRanks = array_map(fn (Card $card) => $card->rank, $hand);
        $cardSuits = array_map(fn (Card $card) => $card->suit, $hand);

        // Group by rank for counting pairs, three-of-a-kind, etc.
        $rankValues = array_map(fn (CardRank $rank) => $rank->getValue(), $cardRanks);
        $rankCounts = array_count_values($rankValues);

        $isFlush = \count(array_unique(array_map(fn (CardSuit $suit) => $suit->value, $cardSuits))) === 1;
        $isStraight = $this->isStraight($cardRanks);

        // Sort ranks: first by count (descending), then by value (descending)
        // This ensures proper kicker order for all hand types
        $sortedRanks = $rankValues;
        usort($sortedRanks, function (int $a, int $b) use ($rankCounts) {
            $countDiff = $rankCounts[$b] <=> $rankCounts[$a];
            if ($countDiff !== 0) {
                return $countDiff;
            }

            return $b <=> $a; // Same count, sort by value descending
        });

        // Remove duplicates while preserving order
        $uniqueRanks = [];
        foreach ($sortedRanks as $rank) {
            if (!\in_array($rank, $uniqueRanks, true)) {
                $uniqueRanks[] = $rank;
            }
        }

        $sortedKickerRanks = array_map(fn (int $rankValue) => CardRank::fromValue($rankValue), $uniqueRanks);

        if ($isFlush && $isStraight) {
            // Check if it's a Royal Flush (10-J-Q-K-A straight flush)
            $values = array_map(fn (CardRank $rank) => $rank->getValue(), $sortedKickerRanks);
            sort($values);
            if ($values === [10, 11, 12, 13, 14]) {
                return [HandRank::ROYAL_FLUSH, $sortedKickerRanks];
            }

            return [HandRank::STRAIGHT_FLUSH, $sortedKickerRanks];
        }

        if (\in_array(4, $rankCounts)) {
            return [HandRank::FOUR_OF_A_KIND, $sortedKickerRanks];
        }

        if (\in_array(3, $rankCounts) && \in_array(2, $rankCounts)) {
            return [HandRank::FULL_HOUSE, $sortedKickerRanks];
        }

        if ($isFlush) {
            return [HandRank::FLUSH, $sortedKickerRanks];
        }

        if ($isStraight) {
            return [HandRank::STRAIGHT, $sortedKickerRanks];
        }

        if (\in_array(3, $rankCounts)) {
            return [HandRank::THREE_OF_A_KIND, $sortedKickerRanks];
        }

        $pairCount = \count(array_filter($rankCounts, fn (int $c) => $c === 2));

        if ($pairCount === 2) {
            return [HandRank::TWO_PAIR, $sortedKickerRanks];
        }

        if ($pairCount === 1) {
            return [HandRank::ONE_PAIR, $sortedKickerRanks];
        }

        return [HandRank::HIGH_CARD, $sortedKickerRanks];
    }

    /**
     * Checks if 5 card ranks form a straight (consecutive values).
     * Handles both regular straights (3-4-5-6-7) and wheel (A-2-3-4-5).
     *
     * @param array<CardRank> $ranks 5 CardRank enums
     */
    private function isStraight(array $ranks): bool
    {
        // Get numeric values and sort
        $values = array_map(fn (CardRank $rank) => $rank->getValue(), $ranks);
        sort($values);

        // Check for wheel straight (A-2-3-4-5): values will be [2,3,4,5,14]
        if ($values === [2, 3, 4, 5, 14]) {
            return true;
        }

        // Check for regular consecutive straight
        for ($i = 0; $i < 4; $i++) {
            if ($values[$i + 1] - $values[$i] !== 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * Generates all possible combinations of N cards from a set.
     * Used to find all 5-card combinations from hole + community cards.
     *
     * @param array<Card> $cards Input Card objects
     * @param int         $size  Number of cards per combination (typically 5)
     *
     * @return array<array<Card>> Array of card combinations
     */
    private function getCombinations(array $cards, int $size): array
    {
        if ($size === 0) {
            return [[]];
        }
        if (\count($cards) === 0) {
            return [];
        }

        $head = array_shift($cards);
        $combinations = [];

        foreach ($this->getCombinations($cards, $size - 1) as $combination) {
            $combinations[] = array_merge([$head], $combination);
        }

        foreach ($this->getCombinations($cards, $size) as $combination) {
            $combinations[] = $combination;
        }

        return $combinations;
    }

    /**
     * Compares two sets of CardRank kickers for Hand objects.
     * Compares rank by rank from highest to lowest until a difference is found.
     *
     * @param array<CardRank> $kickers1 First hand's kicker ranks (sorted desc)
     * @param array<CardRank> $kickers2 Second hand's kicker ranks (sorted desc)
     *
     * @return int Negative if kickers1 < kickers2, 0 if equal, positive if kickers1 > kickers2
     */
    private function compareKickerRanks(array $kickers1, array $kickers2): int
    {
        for ($i = 0; $i < min(\count($kickers1), \count($kickers2)); $i++) {
            $val1 = $kickers1[$i]->getValue();
            $val2 = $kickers2[$i]->getValue();

            if ($val1 !== $val2) {
                return $val1 <=> $val2;
            }
        }

        return 0;
    }
}
