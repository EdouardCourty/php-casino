<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Poker\Service;

use Ecourty\PHPCasino\Poker\Enum\EquityCalculationMethod;
use Ecourty\PHPCasino\Poker\Exception\InvalidEquityInputException;
use Ecourty\PHPCasino\Poker\Model\Card;
use Ecourty\PHPCasino\Poker\Model\EquityResult;
use Ecourty\PHPCasino\Poker\Model\PlayerRange;
use Ecourty\PHPCasino\Poker\Service\EquityCalculator\EnumerationCalculator;
use Ecourty\PHPCasino\Poker\Service\EquityCalculator\MonteCarloSimulator;

/**
 * Main equity calculator service.
 * Calculates the probability of winning a poker hand.
 */
final readonly class EquityCalculator
{
    public function __construct(
        private MonteCarloSimulator $monteCarloSimulator,
        private EnumerationCalculator $enumerationCalculator,
    ) {
    }

    /**
     * Calculates equity for a given poker situation.
     *
     * @param array<string|Card> $heroHoleCards Hero's 2 hole cards
     * @param array<PlayerRange> $opponentRanges Array of opponent ranges (at least 1)
     * @param array<string|Card> $communityCards 0-5 community cards
     * @param EquityCalculationMethod $method Monte Carlo or Enumeration
     * @param int $iterations Number of simulations (for Monte Carlo only, default 10000)
     *
     * @throws InvalidEquityInputException If input is invalid
     */
    public function calculate(
        array $heroHoleCards,
        array $opponentRanges,
        array $communityCards = [],
        EquityCalculationMethod $method = EquityCalculationMethod::MONTE_CARLO,
        int $iterations = 10000,
    ): EquityResult {
        // Convert strings to Cards
        $heroCards = $this->convertToCards($heroHoleCards);
        $communityCardsObj = $this->convertToCards($communityCards);

        // Validate input
        $this->validateInput($heroCards, $opponentRanges, $communityCardsObj, $iterations);

        // Calculate equity using selected method
        if ($method === EquityCalculationMethod::MONTE_CARLO) {
            $stats = $this->monteCarloSimulator->simulate($heroCards, $opponentRanges, $communityCardsObj, $iterations);
        } else {
            $stats = $this->enumerationCalculator->calculate($heroCards, $opponentRanges, $communityCardsObj);
        }

        // Convert raw statistics to EquityResult
        return $this->buildResult($stats);
    }

    /**
     * Converts array of strings/Cards to array of Cards.
     *
     * @param array<string|Card> $cards
     *
     * @return array<Card>
     */
    private function convertToCards(array $cards): array
    {
        return array_map(
            static fn (string|Card $card) => $card instanceof Card ? $card : Card::fromString($card),
            $cards,
        );
    }

    /**
     * Validates input parameters.
     *
     * @param array<Card> $heroCards
     * @param array<PlayerRange> $opponentRanges
     * @param array<Card> $communityCards
     *
     * @throws InvalidEquityInputException
     */
    private function validateInput(array $heroCards, array $opponentRanges, array $communityCards, int $iterations): void
    {
        // Validate hero has exactly 2 cards
        if (\count($heroCards) !== 2) {
            throw InvalidEquityInputException::invalidHoleCardsCount(\count($heroCards));
        }

        // Validate community cards count
        if (\count($communityCards) > 5) {
            throw InvalidEquityInputException::invalidCommunityCardsCount(\count($communityCards));
        }

        // Validate at least one opponent
        if (empty($opponentRanges)) {
            throw InvalidEquityInputException::noOpponents();
        }

        // Validate opponent ranges are not empty
        foreach ($opponentRanges as $index => $range) {
            if ($range->count() === 0) {
                throw InvalidEquityInputException::emptyOpponentRange($index);
            }
        }

        // Validate iterations for Monte Carlo
        if ($iterations <= 0) {
            throw InvalidEquityInputException::invalidIterations($iterations);
        }

        // Check for duplicate cards
        $this->checkDuplicateCards($heroCards, $opponentRanges, $communityCards);
    }

    /**
     * Checks for duplicate cards across all known cards.
     *
     * @param array<Card> $heroCards
     * @param array<PlayerRange> $opponentRanges
     * @param array<Card> $communityCards
     *
     * @throws InvalidEquityInputException If duplicates are found
     */
    private function checkDuplicateCards(array $heroCards, array $opponentRanges, array $communityCards): void
    {
        $seenCards = [];

        // Check hero cards
        foreach ($heroCards as $card) {
            $key = $card->toString();
            if (isset($seenCards[$key])) {
                throw InvalidEquityInputException::duplicateCards($key);
            }
            $seenCards[$key] = true;
        }

        // Check community cards
        foreach ($communityCards as $card) {
            $key = $card->toString();
            if (isset($seenCards[$key])) {
                throw InvalidEquityInputException::duplicateCards($key);
            }
            $seenCards[$key] = true;
        }

        // Check opponent specific hands (only if range has one hand)
        foreach ($opponentRanges as $range) {
            if ($range->isSpecificHand()) {
                $specificHand = $range->getSpecificHand();
                if ($specificHand !== null) {
                    foreach ($specificHand as $card) {
                        $key = $card->toString();
                        if (isset($seenCards[$key])) {
                            throw InvalidEquityInputException::duplicateCards($key);
                        }
                        $seenCards[$key] = true;
                    }
                }
            }
        }
    }

    /**
     * Builds EquityResult from raw statistics.
     *
     * @param array{wins: int, ties: int, total: int} $stats
     */
    private function buildResult(array $stats): EquityResult
    {
        $total = $stats['total'];

        if ($total === 0) {
            return new EquityResult(0.0, 0.0, 0);
        }

        $winProbability = $stats['wins'] / $total;
        $tieProbability = $stats['ties'] / $total;

        return new EquityResult($winProbability, $tieProbability, $total);
    }
}
