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

final class ExactProbabilityCalculator
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
        GameRules $rules
    ): ProbabilityResult {
        $playerHand->validateNotEmpty();

        $availableCards = $this->getAvailableCards($shoe, $knownCards);

        if (empty($availableCards)) {
            throw new \InvalidArgumentException('No cards available in shoe for calculation.');
        }

        $wins = 0;
        $losses = 0;
        $pushes = 0;
        $totalScenarios = 0;

        $cardCounts = $this->countCards($availableCards);

        foreach ($cardCounts as $cardString => $count) {
            $holeCard = Card::fromString($cardString);

            $dealerHand = Hand::fromCards($dealerUpCard, $holeCard);

            $this->playDealerHand($dealerHand, $availableCards, $rules);

            $result = $this->evaluator->compare($playerHand, $dealerHand, $rules);

            if ($result->isPlayerVictory()) {
                $wins += $count;
            } elseif ($result->isDealerVictory()) {
                $losses += $count;
            } else {
                $pushes += $count;
            }

            $totalScenarios += $count;
        }

        return new ProbabilityResult(
            winProbability: $wins / $totalScenarios,
            lossProbability: $losses / $totalScenarios,
            pushProbability: $pushes / $totalScenarios,
            expectedValue: ($wins - $losses) / $totalScenarios,
            scenariosConsidered: $totalScenarios,
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
     * @param array<Card> $cards
     * @return array<string, int>
     */
    private function countCards(array $cards): array
    {
        $counts = [];

        foreach ($cards as $card) {
            $cardString = $card->toString();
            $counts[$cardString] = ($counts[$cardString] ?? 0) + 1;
        }

        return $counts;
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

            $nextCard = $availableCards[0];
            $dealerHand->addCard($nextCard);

            if ($this->evaluator->isBust($dealerHand)) {
                break;
            }
        }
    }
}
