<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Blackjack\Service;

use Ecourty\PHPCasino\Blackjack\Enum\ProbabilityCalculationMethod;
use Ecourty\PHPCasino\Blackjack\Model\GameRules;
use Ecourty\PHPCasino\Blackjack\Model\Hand;
use Ecourty\PHPCasino\Blackjack\Model\ProbabilityResult;
use Ecourty\PHPCasino\Blackjack\Model\Shoe;
use Ecourty\PHPCasino\Blackjack\Service\ProbabilityCalculator\ExactProbabilityCalculator;
use Ecourty\PHPCasino\Blackjack\Service\ProbabilityCalculator\MonteCarloProbabilityCalculator;
use Ecourty\PHPCasino\Common\Model\Card;

final readonly class ProbabilityCalculator
{
    public function __construct(
        private ExactProbabilityCalculator $exactCalculator,
        private MonteCarloProbabilityCalculator $monteCarloCalculator,
    ) {
    }

    /**
     * Factory method to create a ProbabilityCalculator with default implementations.
     */
    public static function create(): self
    {
        return new self(
            new ExactProbabilityCalculator(),
            new MonteCarloProbabilityCalculator(),
        );
    }

    /**
     * @param array<Card> $knownCards
     */
    public function calculate(
        Hand $playerHand,
        Card $dealerUpCard,
        array $knownCards,
        Shoe $shoe,
        GameRules $rules,
        ProbabilityCalculationMethod $method = ProbabilityCalculationMethod::MONTE_CARLO,
        int $iterations = 10000,
    ): ProbabilityResult {
        return match ($method) {
            ProbabilityCalculationMethod::ENUMERATION => $this->exactCalculator->calculate(
                $playerHand,
                $dealerUpCard,
                $knownCards,
                $shoe,
                $rules,
            ),
            ProbabilityCalculationMethod::MONTE_CARLO => $this->monteCarloCalculator->calculate(
                $playerHand,
                $dealerUpCard,
                $knownCards,
                $shoe,
                $rules,
                $iterations,
            ),
        };
    }
}
