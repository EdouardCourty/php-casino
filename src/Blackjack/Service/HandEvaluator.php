<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Blackjack\Service;

use Ecourty\PHPCasino\Blackjack\Enum\GameResult;
use Ecourty\PHPCasino\Blackjack\Enum\HandType;
use Ecourty\PHPCasino\Blackjack\Model\GameRules;
use Ecourty\PHPCasino\Blackjack\Model\Hand;
use Ecourty\PHPCasino\Common\Enum\CardRank;

final class HandEvaluator
{
    private const int BLACKJACK_VALUE = 21;
    private const int ACE_HIGH_VALUE = 11;
    private const int ACE_LOW_VALUE = 1;
    private const int TEN_VALUE = 10;

    public function getHandValue(Hand $hand): int
    {
        $hand->validateNotEmpty();

        $cards = $hand->getCards();
        $value = 0;
        $aceCount = 0;

        foreach ($cards as $card) {
            if ($card->rank === CardRank::ACE) {
                $aceCount++;
                $value += self::ACE_HIGH_VALUE;
            } elseif ($card->rank->isFaceCard() || $card->rank === CardRank::TEN) {
                $value += self::TEN_VALUE;
            } else {
                $value += $card->rank->getValue();
            }
        }

        while ($value > self::BLACKJACK_VALUE && $aceCount > 0) {
            $value -= (self::ACE_HIGH_VALUE - self::ACE_LOW_VALUE);
            $aceCount--;
        }

        return $value;
    }

    public function getHandType(Hand $hand): HandType
    {
        $hand->validateNotEmpty();

        if ($this->isBlackjack($hand)) {
            return HandType::BLACKJACK;
        }

        $value = $this->getHandValue($hand);

        if ($value > self::BLACKJACK_VALUE) {
            return HandType::BUST;
        }

        if ($this->isSoft($hand)) {
            return HandType::SOFT;
        }

        return HandType::HARD;
    }

    public function isBust(Hand $hand): bool
    {
        return $this->getHandValue($hand) > self::BLACKJACK_VALUE;
    }

    public function isBlackjack(Hand $hand): bool
    {
        if ($hand->getCardCount() !== 2) {
            return false;
        }

        $cards = $hand->getCards();
        $hasAce = false;
        $hasTenValue = false;

        foreach ($cards as $card) {
            if ($card->rank === CardRank::ACE) {
                $hasAce = true;
            } elseif ($card->rank->isFaceCard() || $card->rank === CardRank::TEN) {
                $hasTenValue = true;
            }
        }

        return $hasAce && $hasTenValue;
    }

    public function isSoft(Hand $hand): bool
    {
        $hand->validateNotEmpty();

        $cards = $hand->getCards();
        $value = 0;
        $hasUsableAce = false;

        foreach ($cards as $card) {
            if ($card->rank === CardRank::ACE) {
                if ($value + self::ACE_HIGH_VALUE <= self::BLACKJACK_VALUE) {
                    $value += self::ACE_HIGH_VALUE;
                    $hasUsableAce = true;
                } else {
                    $value += self::ACE_LOW_VALUE;
                }
            } elseif ($card->rank->isFaceCard() || $card->rank === CardRank::TEN) {
                $value += self::TEN_VALUE;
            } else {
                $value += $card->rank->getValue();
            }

            if ($value > self::BLACKJACK_VALUE && $hasUsableAce) {
                $value -= (self::ACE_HIGH_VALUE - self::ACE_LOW_VALUE);
                $hasUsableAce = false;
            }
        }

        return $hasUsableAce && $value <= self::BLACKJACK_VALUE;
    }

    public function compare(Hand $playerHand, Hand $dealerHand, ?GameRules $rules = null): GameResult
    {
        $playerHand->validateNotEmpty();
        $dealerHand->validateNotEmpty();

        $playerBlackjack = $this->isBlackjack($playerHand);
        $dealerBlackjack = $this->isBlackjack($dealerHand);

        if ($playerBlackjack && $dealerBlackjack) {
            return GameResult::PUSH;
        }

        if ($playerBlackjack) {
            return GameResult::PLAYER_BLACKJACK;
        }

        if ($dealerBlackjack) {
            return GameResult::DEALER_BLACKJACK;
        }

        $playerValue = $this->getHandValue($playerHand);
        $dealerValue = $this->getHandValue($dealerHand);

        if ($playerValue > self::BLACKJACK_VALUE) {
            return GameResult::PLAYER_BUST;
        }

        if ($dealerValue > self::BLACKJACK_VALUE) {
            return GameResult::DEALER_BUST;
        }

        if ($playerValue > $dealerValue) {
            return GameResult::PLAYER_WIN;
        }

        if ($dealerValue > $playerValue) {
            return GameResult::DEALER_WIN;
        }

        return GameResult::PUSH;
    }

    public function shouldDealerHit(Hand $dealerHand, GameRules $rules): bool
    {
        $dealerHand->validateNotEmpty();

        $value = $this->getHandValue($dealerHand);

        if ($value < $rules->getDealerStandThreshold()) {
            return true;
        }

        if ($value === 17 && $rules->dealerHitsOnSoft17 && $this->isSoft($dealerHand)) {
            return true;
        }

        return false;
    }
}
