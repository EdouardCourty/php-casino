<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Tests\Unit\Blackjack\Service;

use Ecourty\PHPCasino\Blackjack\Enum\GameResult;
use Ecourty\PHPCasino\Blackjack\Enum\HandType;
use Ecourty\PHPCasino\Blackjack\Model\GameRules;
use Ecourty\PHPCasino\Blackjack\Model\Hand;
use Ecourty\PHPCasino\Blackjack\Service\HandEvaluator;
use Ecourty\PHPCasino\Common\Enum\CardRank;
use Ecourty\PHPCasino\Common\Enum\CardSuit;
use Ecourty\PHPCasino\Common\Model\Card;
use PHPUnit\Framework\TestCase;

final class HandEvaluatorTest extends TestCase
{
    private HandEvaluator $evaluator;

    protected function setUp(): void
    {
        $this->evaluator = new HandEvaluator();
    }

    public function testGetHandValueWithSimpleHand(): void
    {
        $hand = Hand::fromCards(
            new Card(CardRank::TEN, CardSuit::SPADES),
            new Card(CardRank::FIVE, CardSuit::HEARTS),
        );

        $this->assertSame(15, $this->evaluator->getHandValue($hand));
    }

    public function testGetHandValueWithFaceCards(): void
    {
        $hand = Hand::fromCards(
            new Card(CardRank::KING, CardSuit::SPADES),
            new Card(CardRank::QUEEN, CardSuit::HEARTS),
        );

        $this->assertSame(20, $this->evaluator->getHandValue($hand));
    }

    public function testGetHandValueWithSoftAce(): void
    {
        $hand = Hand::fromCards(
            new Card(CardRank::ACE, CardSuit::SPADES),
            new Card(CardRank::SIX, CardSuit::HEARTS),
        );

        $this->assertSame(17, $this->evaluator->getHandValue($hand));
    }

    public function testGetHandValueWithHardAce(): void
    {
        $hand = Hand::fromCards(
            new Card(CardRank::ACE, CardSuit::SPADES),
            new Card(CardRank::KING, CardSuit::HEARTS),
            new Card(CardRank::FIVE, CardSuit::CLUBS),
        );

        $this->assertSame(16, $this->evaluator->getHandValue($hand));
    }

    public function testGetHandValueWithMultipleAces(): void
    {
        $hand = Hand::fromCards(
            new Card(CardRank::ACE, CardSuit::SPADES),
            new Card(CardRank::ACE, CardSuit::HEARTS),
            new Card(CardRank::NINE, CardSuit::CLUBS),
        );

        $this->assertSame(21, $this->evaluator->getHandValue($hand));
    }

    public function testGetHandValueBlackjack(): void
    {
        $hand = Hand::fromCards(
            new Card(CardRank::ACE, CardSuit::SPADES),
            new Card(CardRank::KING, CardSuit::HEARTS),
        );

        $this->assertSame(21, $this->evaluator->getHandValue($hand));
    }

    public function testGetHandValueBust(): void
    {
        $hand = Hand::fromCards(
            new Card(CardRank::KING, CardSuit::SPADES),
            new Card(CardRank::QUEEN, CardSuit::HEARTS),
            new Card(CardRank::FIVE, CardSuit::CLUBS),
        );

        $this->assertSame(25, $this->evaluator->getHandValue($hand));
    }

    public function testIsBlackjackTrue(): void
    {
        $hand = Hand::fromCards(
            new Card(CardRank::ACE, CardSuit::SPADES),
            new Card(CardRank::JACK, CardSuit::HEARTS),
        );

        $this->assertTrue($this->evaluator->isBlackjack($hand));
    }

    public function testIsBlackjackFalseWith21ButThreeCards(): void
    {
        $hand = Hand::fromCards(
            new Card(CardRank::SEVEN, CardSuit::SPADES),
            new Card(CardRank::SEVEN, CardSuit::HEARTS),
            new Card(CardRank::SEVEN, CardSuit::CLUBS),
        );

        $this->assertFalse($this->evaluator->isBlackjack($hand));
    }

    public function testIsBustTrue(): void
    {
        $hand = Hand::fromCards(
            new Card(CardRank::KING, CardSuit::SPADES),
            new Card(CardRank::QUEEN, CardSuit::HEARTS),
            new Card(CardRank::FIVE, CardSuit::CLUBS),
        );

        $this->assertTrue($this->evaluator->isBust($hand));
    }

    public function testIsBustFalse(): void
    {
        $hand = Hand::fromCards(
            new Card(CardRank::TEN, CardSuit::SPADES),
            new Card(CardRank::FIVE, CardSuit::HEARTS),
        );

        $this->assertFalse($this->evaluator->isBust($hand));
    }

    public function testIsSoftTrue(): void
    {
        $hand = Hand::fromCards(
            new Card(CardRank::ACE, CardSuit::SPADES),
            new Card(CardRank::SIX, CardSuit::HEARTS),
        );

        $this->assertTrue($this->evaluator->isSoft($hand));
    }

    public function testIsSoftFalseWithHardAce(): void
    {
        $hand = Hand::fromCards(
            new Card(CardRank::ACE, CardSuit::SPADES),
            new Card(CardRank::KING, CardSuit::HEARTS),
            new Card(CardRank::FIVE, CardSuit::CLUBS),
        );

        $this->assertFalse($this->evaluator->isSoft($hand));
    }

    public function testGetHandTypeBlackjack(): void
    {
        $hand = Hand::fromCards(
            new Card(CardRank::ACE, CardSuit::SPADES),
            new Card(CardRank::KING, CardSuit::HEARTS),
        );

        $this->assertSame(HandType::BLACKJACK, $this->evaluator->getHandType($hand));
    }

    public function testGetHandTypeBust(): void
    {
        $hand = Hand::fromCards(
            new Card(CardRank::KING, CardSuit::SPADES),
            new Card(CardRank::QUEEN, CardSuit::HEARTS),
            new Card(CardRank::FIVE, CardSuit::CLUBS),
        );

        $this->assertSame(HandType::BUST, $this->evaluator->getHandType($hand));
    }

    public function testGetHandTypeSoft(): void
    {
        $hand = Hand::fromCards(
            new Card(CardRank::ACE, CardSuit::SPADES),
            new Card(CardRank::SIX, CardSuit::HEARTS),
        );

        $this->assertSame(HandType::SOFT, $this->evaluator->getHandType($hand));
    }

    public function testGetHandTypeHard(): void
    {
        $hand = Hand::fromCards(
            new Card(CardRank::TEN, CardSuit::SPADES),
            new Card(CardRank::SEVEN, CardSuit::HEARTS),
        );

        $this->assertSame(HandType::HARD, $this->evaluator->getHandType($hand));
    }

    public function testComparePlayerBlackjackWins(): void
    {
        $playerHand = Hand::fromCards(
            new Card(CardRank::ACE, CardSuit::SPADES),
            new Card(CardRank::KING, CardSuit::HEARTS),
        );

        $dealerHand = Hand::fromCards(
            new Card(CardRank::TEN, CardSuit::CLUBS),
            new Card(CardRank::NINE, CardSuit::DIAMONDS),
        );

        $result = $this->evaluator->compare($playerHand, $dealerHand);

        $this->assertSame(GameResult::PLAYER_BLACKJACK, $result);
    }

    public function testCompareDealerBlackjackWins(): void
    {
        $playerHand = Hand::fromCards(
            new Card(CardRank::TEN, CardSuit::SPADES),
            new Card(CardRank::NINE, CardSuit::HEARTS),
        );

        $dealerHand = Hand::fromCards(
            new Card(CardRank::ACE, CardSuit::CLUBS),
            new Card(CardRank::KING, CardSuit::DIAMONDS),
        );

        $result = $this->evaluator->compare($playerHand, $dealerHand);

        $this->assertSame(GameResult::DEALER_BLACKJACK, $result);
    }

    public function testCompareBothBlackjackPush(): void
    {
        $playerHand = Hand::fromCards(
            new Card(CardRank::ACE, CardSuit::SPADES),
            new Card(CardRank::KING, CardSuit::HEARTS),
        );

        $dealerHand = Hand::fromCards(
            new Card(CardRank::ACE, CardSuit::CLUBS),
            new Card(CardRank::QUEEN, CardSuit::DIAMONDS),
        );

        $result = $this->evaluator->compare($playerHand, $dealerHand);

        $this->assertSame(GameResult::PUSH, $result);
    }

    public function testComparePlayerWins(): void
    {
        $playerHand = Hand::fromCards(
            new Card(CardRank::TEN, CardSuit::SPADES),
            new Card(CardRank::NINE, CardSuit::HEARTS),
        );

        $dealerHand = Hand::fromCards(
            new Card(CardRank::TEN, CardSuit::CLUBS),
            new Card(CardRank::SEVEN, CardSuit::DIAMONDS),
        );

        $result = $this->evaluator->compare($playerHand, $dealerHand);

        $this->assertSame(GameResult::PLAYER_WIN, $result);
    }

    public function testCompareDealerWins(): void
    {
        $playerHand = Hand::fromCards(
            new Card(CardRank::TEN, CardSuit::SPADES),
            new Card(CardRank::SEVEN, CardSuit::HEARTS),
        );

        $dealerHand = Hand::fromCards(
            new Card(CardRank::TEN, CardSuit::CLUBS),
            new Card(CardRank::NINE, CardSuit::DIAMONDS),
        );

        $result = $this->evaluator->compare($playerHand, $dealerHand);

        $this->assertSame(GameResult::DEALER_WIN, $result);
    }

    public function testComparePush(): void
    {
        $playerHand = Hand::fromCards(
            new Card(CardRank::TEN, CardSuit::SPADES),
            new Card(CardRank::EIGHT, CardSuit::HEARTS),
        );

        $dealerHand = Hand::fromCards(
            new Card(CardRank::NINE, CardSuit::CLUBS),
            new Card(CardRank::NINE, CardSuit::DIAMONDS),
        );

        $result = $this->evaluator->compare($playerHand, $dealerHand);

        $this->assertSame(GameResult::PUSH, $result);
    }

    public function testComparePlayerBust(): void
    {
        $playerHand = Hand::fromCards(
            new Card(CardRank::KING, CardSuit::SPADES),
            new Card(CardRank::QUEEN, CardSuit::HEARTS),
            new Card(CardRank::FIVE, CardSuit::CLUBS),
        );

        $dealerHand = Hand::fromCards(
            new Card(CardRank::TEN, CardSuit::CLUBS),
            new Card(CardRank::SEVEN, CardSuit::DIAMONDS),
        );

        $result = $this->evaluator->compare($playerHand, $dealerHand);

        $this->assertSame(GameResult::PLAYER_BUST, $result);
    }

    public function testCompareDealerBust(): void
    {
        $playerHand = Hand::fromCards(
            new Card(CardRank::TEN, CardSuit::SPADES),
            new Card(CardRank::SEVEN, CardSuit::HEARTS),
        );

        $dealerHand = Hand::fromCards(
            new Card(CardRank::KING, CardSuit::CLUBS),
            new Card(CardRank::QUEEN, CardSuit::DIAMONDS),
            new Card(CardRank::FIVE, CardSuit::SPADES),
        );

        $result = $this->evaluator->compare($playerHand, $dealerHand);

        $this->assertSame(GameResult::DEALER_BUST, $result);
    }

    public function testShouldDealerHitBelow17(): void
    {
        $dealerHand = Hand::fromCards(
            new Card(CardRank::TEN, CardSuit::SPADES),
            new Card(CardRank::FIVE, CardSuit::HEARTS),
        );

        $rules = GameRules::standard();

        $this->assertTrue($this->evaluator->shouldDealerHit($dealerHand, $rules));
    }

    public function testShouldDealerStandOn17(): void
    {
        $dealerHand = Hand::fromCards(
            new Card(CardRank::TEN, CardSuit::SPADES),
            new Card(CardRank::SEVEN, CardSuit::HEARTS),
        );

        $rules = GameRules::standard();

        $this->assertFalse($this->evaluator->shouldDealerHit($dealerHand, $rules));
    }

    public function testShouldDealerHitOnSoft17WhenRuleEnabled(): void
    {
        $dealerHand = Hand::fromCards(
            new Card(CardRank::ACE, CardSuit::SPADES),
            new Card(CardRank::SIX, CardSuit::HEARTS),
        );

        $rules = new GameRules(dealerHitsOnSoft17: true);

        $this->assertTrue($this->evaluator->shouldDealerHit($dealerHand, $rules));
    }

    public function testShouldDealerStandOnSoft17WhenRuleDisabled(): void
    {
        $dealerHand = Hand::fromCards(
            new Card(CardRank::ACE, CardSuit::SPADES),
            new Card(CardRank::SIX, CardSuit::HEARTS),
        );

        $rules = new GameRules(dealerHitsOnSoft17: false);

        $this->assertFalse($this->evaluator->shouldDealerHit($dealerHand, $rules));
    }
}
