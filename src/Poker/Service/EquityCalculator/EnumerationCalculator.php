<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Poker\Service\EquityCalculator;

use Ecourty\PHPCasino\Common\Model\Card;
use Ecourty\PHPCasino\Common\Model\Deck;
use Ecourty\PHPCasino\Poker\Exception\CardNotFoundException;
use Ecourty\PHPCasino\Poker\Exception\InvalidCardNotationException;
use Ecourty\PHPCasino\Poker\Exception\InvalidRankException;
use Ecourty\PHPCasino\Poker\Exception\InvalidSuitException;
use Ecourty\PHPCasino\Poker\Exception\NoValidHandFoundException;
use Ecourty\PHPCasino\Poker\Model\Hand;
use Ecourty\PHPCasino\Poker\Model\PlayerRange;
use Ecourty\PHPCasino\Poker\Service\HandEvaluator;

/**
 * Complete enumeration calculator for poker equity.
 * Evaluates all possible card combinations for exact results.
 */
final readonly class EnumerationCalculator
{
    public function __construct(
        private HandEvaluator $handEvaluator,
    ) {
    }

    /**
     * Calculates exact equity by enumerating all possibilities.
     *
     * @param array<Card> $heroHoleCards Hero's 2 hole cards
     * @param array<PlayerRange> $opponentRanges Opponent ranges
     * @param array<Card> $communityCards 0-5 community cards already dealt
     *
     * @throws CardNotFoundException If a specified card is not found in the deck
     * @throws InvalidCardNotationException If a card notation is invalid
     * @throws InvalidRankException If a card rank is invalid
     * @throws InvalidSuitException If a card suit is invalid
     * @throws NoValidHandFoundException If no valid hand can be formed with the given cards
     *
     * @return array{wins: int, ties: int, total: int} Raw statistics
     */
    public function calculate(array $heroHoleCards, array $opponentRanges, array $communityCards): array
    {
        $wins = 0;
        $ties = 0;
        $total = 0;

        // Create deck with unknown cards
        $deck = new Deck();
        $knownCards = array_merge($heroHoleCards, $communityCards);

        foreach ($knownCards as $card) {
            $deck->removeCard($card, throwIfNotFound: false);
        }

        $unknownCards = $deck->getRemainingCards();

        // Generate all opponent hand combinations
        $allOpponentCombinations = $this->generateOpponentCombinations($opponentRanges, $unknownCards);

        foreach ($allOpponentCombinations as $opponentHands) {
            // Remove opponent cards from available cards
            $usedCards = $knownCards;
            foreach ($opponentHands as $opponentHand) {
                $usedCards[] = $opponentHand[0];
                $usedCards[] = $opponentHand[1];
            }

            // Get remaining cards for community
            $availableForCommunity = $this->getAvailableCards($unknownCards, $usedCards);

            // Generate all community card completions
            $cardsNeeded = 5 - \count($communityCards);
            $communityCombinations = $this->getCombinations($availableForCommunity, $cardsNeeded);

            foreach ($communityCombinations as $additionalCommunity) {
                $completedCommunity = array_merge($communityCards, $additionalCommunity);

                // Evaluate all hands
                $heroHand = $this->handEvaluator->evaluateBestHand($heroHoleCards, $completedCommunity);
                $opponentEvaluatedHands = [];

                foreach ($opponentHands as $opponentHoleCards) {
                    $opponentEvaluatedHands[] = $this->handEvaluator->evaluateBestHand($opponentHoleCards, $completedCommunity);
                }

                // Determine result
                $result = $this->determineResult($heroHand, $opponentEvaluatedHands);

                if ($result === 'win') {
                    $wins++;
                } elseif ($result === 'tie') {
                    $ties++;
                }

                $total++;
            }
        }

        return [
            'wins' => $wins,
            'ties' => $ties,
            'total' => $total,
        ];
    }

    /**
     * Generates all possible opponent hand combinations from ranges.
     *
     * @param array<PlayerRange> $ranges
     * @param array<Card> $availableCards
     *
     * @return array<array<array{0: Card, 1: Card}>> All possible opponent hand combinations
     */
    private function generateOpponentCombinations(array $ranges, array $availableCards): array
    {
        if (empty($ranges)) {
            return [[]];
        }

        $firstRange = array_shift($ranges);
        $restCombinations = $this->generateOpponentCombinations($ranges, $availableCards);

        $allCombinations = [];

        foreach ($firstRange->possibleHands as $hand) {
            // Check if both cards are available
            $card1Available = $this->isCardInArray($hand[0], $availableCards);
            $card2Available = $this->isCardInArray($hand[1], $availableCards);

            if (!$card1Available || !$card2Available) {
                continue;
            }

            foreach ($restCombinations as $restCombo) {
                // Check for conflicts with other opponents in this combination
                $conflict = false;
                foreach ($restCombo as $otherHand) {
                    if ($hand[0]->equals($otherHand[0]) || $hand[0]->equals($otherHand[1]) ||
                        $hand[1]->equals($otherHand[0]) || $hand[1]->equals($otherHand[1])) {
                        $conflict = true;
                        break;
                    }
                }

                if (!$conflict) {
                    $allCombinations[] = array_merge([$hand], $restCombo);
                }
            }
        }

        return $allCombinations;
    }

    /**
     * Gets available cards excluding used cards.
     *
     * @param array<Card> $allCards
     * @param array<Card> $usedCards
     *
     * @return array<Card>
     */
    private function getAvailableCards(array $allCards, array $usedCards): array
    {
        $available = [];

        foreach ($allCards as $card) {
            $used = false;
            foreach ($usedCards as $usedCard) {
                if ($card->equals($usedCard)) {
                    $used = true;
                    break;
                }
            }

            if (!$used) {
                $available[] = $card;
            }
        }

        return $available;
    }

    /**
     * Checks if a card is in an array of cards.
     *
     * @param array<Card> $haystack
     */
    private function isCardInArray(Card $needle, array $haystack): bool
    {
        foreach ($haystack as $card) {
            if ($needle->equals($card)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generates all combinations of N cards from a set.
     *
     * @param array<Card> $cards
     *
     * @return array<array<Card>>
     */
    private function getCombinations(array $cards, int $size): array
    {
        if ($size === 0) {
            return [[]];
        }

        if (empty($cards)) {
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
     * Determines if hero wins, ties, or loses.
     *
     * @param array<Hand> $opponentHands
     *
     * @return string 'win', 'tie', or 'loss'
     */
    private function determineResult(Hand $heroHand, array $opponentHands): string
    {
        $heroWins = true;
        $heroTies = false;

        foreach ($opponentHands as $opponentHand) {
            $comparison = $this->handEvaluator->compareHands($heroHand, $opponentHand);

            if ($comparison < 0) {
                return 'loss';
            }

            if ($comparison === 0) {
                $heroTies = true;
            }

            if ($comparison <= 0) {
                $heroWins = false;
            }
        }

        if ($heroWins) {
            return 'win';
        }

        if ($heroTies) {
            return 'tie';
        }

        return 'loss';
    }
}
