<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Tests\Unit\Blackjack\Service;

use Ecourty\PHPCasino\Blackjack\Enum\ProbabilityCalculationMethod;
use Ecourty\PHPCasino\Blackjack\Model\GameRules;
use Ecourty\PHPCasino\Blackjack\Model\Hand;
use Ecourty\PHPCasino\Blackjack\Model\Shoe;
use Ecourty\PHPCasino\Blackjack\Service\ProbabilityCalculator;
use Ecourty\PHPCasino\Common\Enum\CardRank;
use Ecourty\PHPCasino\Common\Enum\CardSuit;
use Ecourty\PHPCasino\Common\Model\Card;
use PHPUnit\Framework\TestCase;

final class ProbabilityCalculatorTest extends TestCase
{
    private ProbabilityCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new ProbabilityCalculator(
            new \Ecourty\PHPCasino\Blackjack\Service\ProbabilityCalculator\ExactProbabilityCalculator(),
            new \Ecourty\PHPCasino\Blackjack\Service\ProbabilityCalculator\MonteCarloProbabilityCalculator(),
        );
    }

    public function testCalculateWithEnumerationMethod(): void
    {
        $playerHand = Hand::fromCards(
            new Card(CardRank::TEN, CardSuit::SPADES),
            new Card(CardRank::NINE, CardSuit::HEARTS)
        );

        $dealerUpCard = new Card(CardRank::SIX, CardSuit::CLUBS);

        $knownCards = [
            new Card(CardRank::TEN, CardSuit::SPADES),
            new Card(CardRank::NINE, CardSuit::HEARTS),
            new Card(CardRank::SIX, CardSuit::CLUBS),
        ];

        $shoe = new Shoe(1);
        $rules = GameRules::standard();

        $result = $this->calculator->calculate(
            $playerHand,
            $dealerUpCard,
            $knownCards,
            $shoe,
            $rules,
            ProbabilityCalculationMethod::ENUMERATION
        );

        $this->assertGreaterThan(0, $result->scenariosConsidered);
        $this->assertGreaterThanOrEqual(0.0, $result->winProbability);
        $this->assertLessThanOrEqual(1.0, $result->winProbability);
    }

    public function testCalculateWithMonteCarloMethod(): void
    {
        $playerHand = Hand::fromCards(
            new Card(CardRank::KING, CardSuit::SPADES),
            new Card(CardRank::TEN, CardSuit::HEARTS)
        );

        $dealerUpCard = new Card(CardRank::FIVE, CardSuit::CLUBS);

        $knownCards = [
            new Card(CardRank::KING, CardSuit::SPADES),
            new Card(CardRank::TEN, CardSuit::HEARTS),
            new Card(CardRank::FIVE, CardSuit::CLUBS),
        ];

        $shoe = new Shoe(6);
        $rules = GameRules::standard();

        $result = $this->calculator->calculate(
            $playerHand,
            $dealerUpCard,
            $knownCards,
            $shoe,
            $rules,
            ProbabilityCalculationMethod::MONTE_CARLO,
            1000
        );

        $this->assertSame(1000, $result->scenariosConsidered);
        $this->assertGreaterThanOrEqual(0.0, $result->winProbability);
        $this->assertLessThanOrEqual(1.0, $result->winProbability);
    }

    public function testCalculateDefaultsToMonteCarlo(): void
    {
        $playerHand = Hand::fromCards(
            new Card(CardRank::ACE, CardSuit::SPADES),
            new Card(CardRank::KING, CardSuit::HEARTS)
        );

        $dealerUpCard = new Card(CardRank::SEVEN, CardSuit::CLUBS);

        $knownCards = [
            new Card(CardRank::ACE, CardSuit::SPADES),
            new Card(CardRank::KING, CardSuit::HEARTS),
            new Card(CardRank::SEVEN, CardSuit::CLUBS),
        ];

        $shoe = new Shoe(6);
        $rules = GameRules::standard();

        $result = $this->calculator->calculate(
            $playerHand,
            $dealerUpCard,
            $knownCards,
            $shoe,
            $rules
        );

        $this->assertSame(10000, $result->scenariosConsidered);
    }

    public function testCalculateWithCustomIterations(): void
    {
        $playerHand = Hand::fromCards(
            new Card(CardRank::QUEEN, CardSuit::SPADES),
            new Card(CardRank::JACK, CardSuit::HEARTS)
        );

        $dealerUpCard = new Card(CardRank::EIGHT, CardSuit::CLUBS);

        $knownCards = [
            new Card(CardRank::QUEEN, CardSuit::SPADES),
            new Card(CardRank::JACK, CardSuit::HEARTS),
            new Card(CardRank::EIGHT, CardSuit::CLUBS),
        ];

        $shoe = new Shoe(6);
        $rules = GameRules::standard();

        $result = $this->calculator->calculate(
            $playerHand,
            $dealerUpCard,
            $knownCards,
            $shoe,
            $rules,
            ProbabilityCalculationMethod::MONTE_CARLO,
            50000
        );

        $this->assertSame(50000, $result->scenariosConsidered);
    }
}
