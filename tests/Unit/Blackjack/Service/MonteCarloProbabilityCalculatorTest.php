<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Tests\Unit\Blackjack\Service;

use Ecourty\PHPCasino\Blackjack\Model\GameRules;
use Ecourty\PHPCasino\Blackjack\Model\Hand;
use Ecourty\PHPCasino\Blackjack\Model\Shoe;
use Ecourty\PHPCasino\Blackjack\Service\ProbabilityCalculator\MonteCarloProbabilityCalculator;
use Ecourty\PHPCasino\Common\Enum\CardRank;
use Ecourty\PHPCasino\Common\Enum\CardSuit;
use Ecourty\PHPCasino\Common\Model\Card;
use PHPUnit\Framework\TestCase;

final class MonteCarloProbabilityCalculatorTest extends TestCase
{
    private MonteCarloProbabilityCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new MonteCarloProbabilityCalculator();
    }

    public function testCalculateBasicScenario(): void
    {
        $playerHand = Hand::fromCards(
            new Card(CardRank::TEN, CardSuit::SPADES),
            new Card(CardRank::NINE, CardSuit::HEARTS),
        );

        $dealerUpCard = new Card(CardRank::SIX, CardSuit::CLUBS);

        $knownCards = [
            new Card(CardRank::TEN, CardSuit::SPADES),
            new Card(CardRank::NINE, CardSuit::HEARTS),
            new Card(CardRank::SIX, CardSuit::CLUBS),
        ];

        $shoe = new Shoe(6);
        $rules = GameRules::standard();

        $result = $this->calculator->calculate($playerHand, $dealerUpCard, $knownCards, $shoe, $rules, 1000);

        $this->assertSame(1000, $result->scenariosConsidered);
        $this->assertGreaterThanOrEqual(0.0, $result->winProbability);
        $this->assertLessThanOrEqual(1.0, $result->winProbability);
        $this->assertEqualsWithDelta(1.0, $result->winProbability + $result->lossProbability + $result->pushProbability, 0.0001);
    }

    public function testPlayerWith20IsLikelyToWin(): void
    {
        $playerHand = Hand::fromCards(
            new Card(CardRank::KING, CardSuit::SPADES),
            new Card(CardRank::TEN, CardSuit::HEARTS),
        );

        $dealerUpCard = new Card(CardRank::FIVE, CardSuit::CLUBS);

        $knownCards = [
            new Card(CardRank::KING, CardSuit::SPADES),
            new Card(CardRank::TEN, CardSuit::HEARTS),
            new Card(CardRank::FIVE, CardSuit::CLUBS),
        ];

        $shoe = new Shoe(6);
        $rules = GameRules::standard();

        $result = $this->calculator->calculate($playerHand, $dealerUpCard, $knownCards, $shoe, $rules, 5000);

        $this->assertTrue($result->isPlayerFavored());
    }

    public function testThrowsExceptionForInvalidIterations(): void
    {
        $playerHand = Hand::fromCards(
            new Card(CardRank::TEN, CardSuit::SPADES),
            new Card(CardRank::NINE, CardSuit::HEARTS),
        );

        $dealerUpCard = new Card(CardRank::SIX, CardSuit::CLUBS);

        $shoe = new Shoe(6);
        $rules = GameRules::standard();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Iterations must be at least 1');

        $this->calculator->calculate($playerHand, $dealerUpCard, [], $shoe, $rules, 0);
    }

    public function testThrowsExceptionWhenNoCardsAvailable(): void
    {
        $playerHand = Hand::fromCards(
            new Card(CardRank::TEN, CardSuit::SPADES),
            new Card(CardRank::NINE, CardSuit::HEARTS),
        );

        $dealerUpCard = new Card(CardRank::SIX, CardSuit::CLUBS);

        $shoe = new Shoe(1);
        $shoe->draw(52);

        $rules = GameRules::standard();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No cards available in shoe');

        $this->calculator->calculate($playerHand, $dealerUpCard, [], $shoe, $rules, 1000);
    }

    public function testConvergenceWithHighIterations(): void
    {
        $playerHand = Hand::fromCards(
            new Card(CardRank::TEN, CardSuit::SPADES),
            new Card(CardRank::TEN, CardSuit::HEARTS),
        );

        $dealerUpCard = new Card(CardRank::SIX, CardSuit::CLUBS);

        $knownCards = [
            new Card(CardRank::TEN, CardSuit::SPADES),
            new Card(CardRank::TEN, CardSuit::HEARTS),
            new Card(CardRank::SIX, CardSuit::CLUBS),
        ];

        $shoe = new Shoe(1);
        $rules = GameRules::standard();

        $result1 = $this->calculator->calculate($playerHand, $dealerUpCard, $knownCards, $shoe, $rules, 10000);
        $result2 = $this->calculator->calculate($playerHand, $dealerUpCard, $knownCards, $shoe, $rules, 10000);

        $this->assertEqualsWithDelta($result1->winProbability, $result2->winProbability, 0.05);
    }
}
