<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Blackjack\Service\ProbabilityCalculator;

use Ecourty\PHPCasino\Blackjack\Enum\ProbabilityCalculationMethod;
use Ecourty\PHPCasino\Blackjack\Model\GameRules;
use Ecourty\PHPCasino\Blackjack\Model\Hand;
use Ecourty\PHPCasino\Blackjack\Model\ProbabilityResult;
use Ecourty\PHPCasino\Blackjack\Model\Shoe;
use Ecourty\PHPCasino\Blackjack\Service\HandEvaluator;
use Ecourty\PHPCasino\Common\Model\Card;

final class MonteCarloProbabilityCalculator
{
    private readonly HandEvaluator $evaluator;

    public function __construct(?HandEvaluator $evaluator = null)
    {
        $this->evaluator = $evaluator ?? new HandEvaluator();
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
        int $iterations = 10000,
    ): ProbabilityResult {
        $playerHand->validateNotEmpty();

        if ($iterations < 1) {
            throw new \InvalidArgumentException("Iterations must be at least 1, got {$iterations}");
        }

        $availableCards = $this->getAvailableCards($shoe, $knownCards);

        if (empty($availableCards)) {
            throw new \InvalidArgumentException('No cards available in shoe for calculation.');
        }

        $wins = 0;
        $losses = 0;
        $pushes = 0;

        for ($i = 0; $i < $iterations; $i++) {
            $holeCard = $availableCards[array_rand($availableCards)];

            $dealerHand = Hand::fromCards($dealerUpCard, $holeCard);

            $this->playDealerHand($dealerHand, $availableCards, $rules);

            $result = $this->evaluator->compare($playerHand, $dealerHand, $rules);

            if ($result->isPlayerVictory()) {
                $wins++;
            } elseif ($result->isDealerVictory()) {
                $losses++;
            } else {
                $pushes++;
            }
        }

        return new ProbabilityResult(
            winProbability: $wins / $iterations,
            lossProbability: $losses / $iterations,
            pushProbability: $pushes / $iterations,
            expectedValue: ($wins - $losses) / $iterations,
            scenariosConsidered: $iterations,
        );
    }

    /**
     * @param array<Card> $knownCards
     * @return array<Card>
     */
    private function getAvailableCards(Shoe $shoe, array $knownCards): array
    {
        $available = $shoe->getRemainingCards();

        foreach ($knownCards as $knownCard) {
            foreach ($available as $key => $card) {
                if ($card->equals($knownCard)) {
                    unset($available[$key]);
                    break;
                }
            }
        }

        return array_values($available);
    }

    /**
     * @param array<Card> $availableCards
     */
    private function playDealerHand(Hand $dealerHand, array $availableCards, GameRules $rules): void
    {
        while ($this->evaluator->shouldDealerHit($dealerHand, $rules)) {
            if (empty($availableCards)) {
                break;
            }

            $nextCard = $availableCards[array_rand($availableCards)];
            $dealerHand->addCard($nextCard);

            if ($this->evaluator->isBust($dealerHand)) {
                break;
            }
        }
    }
}
