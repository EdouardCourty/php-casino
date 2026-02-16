<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Poker\Service\EquityCalculator;

use Ecourty\PHPCasino\Common\Model\Card;
use Ecourty\PHPCasino\Common\Model\Deck;
use Ecourty\PHPCasino\Poker\Model\Hand;
use Ecourty\PHPCasino\Poker\Model\PlayerRange;
use Ecourty\PHPCasino\Poker\Service\HandEvaluator;

/**
 * Monte Carlo simulator for poker equity calculation.
 * Runs random simulations to estimate win/tie probabilities.
 */
final readonly class MonteCarloSimulator
{
    public function __construct(
        private HandEvaluator $handEvaluator,
    ) {
    }

    /**
     * Runs Monte Carlo simulation to estimate equity.
     *
     * @param array<Card> $heroHoleCards Hero's 2 hole cards
     * @param array<PlayerRange> $opponentRanges Opponent ranges
     * @param array<Card> $communityCards 0-5 community cards already dealt
     * @param int $iterations Number of simulations to run
     *
     * @return array{wins: int, ties: int, total: int} Raw statistics
     */
    public function simulate(array $heroHoleCards, array $opponentRanges, array $communityCards, int $iterations): array
    {
        $wins = 0;
        $ties = 0;

        for ($i = 0; $i < $iterations; $i++) {
            $result = $this->runSingleSimulation($heroHoleCards, $opponentRanges, $communityCards);

            if ($result === 'win') {
                $wins++;
            } elseif ($result === 'tie') {
                $ties++;
            }
        }

        return [
            'wins' => $wins,
            'ties' => $ties,
            'total' => $iterations,
        ];
    }

    /**
     * Runs a single random simulation.
     *
     * @param array<Card> $heroHoleCards
     * @param array<PlayerRange> $opponentRanges
     * @param array<Card> $communityCards
     *
     * @return string 'win', 'tie', or 'loss'
     */
    private function runSingleSimulation(array $heroHoleCards, array $opponentRanges, array $communityCards): string
    {
        // Create a deck and remove all known cards
        $deck = new Deck();
        $knownCards = array_merge($heroHoleCards, $communityCards);

        foreach ($knownCards as $card) {
            $deck->removeCard($card, throwIfNotFound: false);
        }

        // Deal random hands for each opponent from their ranges
        $opponentHands = [];
        foreach ($opponentRanges as $range) {
            // Pick a random hand from the range
            $randomIndex = random_int(0, $range->count() - 1);
            $opponentHand = $range->possibleHands[$randomIndex];

            // Remove these cards from deck
            $deck->removeCard($opponentHand[0], throwIfNotFound: false);
            $deck->removeCard($opponentHand[1], throwIfNotFound: false);

            $opponentHands[] = $opponentHand;
        }

        // Complete the community cards if needed
        $cardsNeeded = 5 - \count($communityCards);
        $completedCommunityCards = $communityCards;

        if ($cardsNeeded > 0 && $deck->count() >= $cardsNeeded) {
            $deck->shuffle();
            $additionalCards = $deck->draw($cardsNeeded);
            $completedCommunityCards = array_merge($communityCards, $additionalCards);
        }

        // Evaluate all hands
        $heroHand = $this->handEvaluator->evaluateBestHand($heroHoleCards, $completedCommunityCards);
        $opponentEvaluatedHands = [];

        foreach ($opponentHands as $opponentHoleCards) {
            $opponentEvaluatedHands[] = $this->handEvaluator->evaluateBestHand($opponentHoleCards, $completedCommunityCards);
        }

        // Determine result
        return $this->determineResult($heroHand, $opponentEvaluatedHands);
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
                // Hero loses to this opponent
                return 'loss';
            }

            if ($comparison === 0) {
                // Hero ties with this opponent
                $heroTies = true;
            }

            if ($comparison <= 0) {
                // Hero doesn't beat this opponent
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
