<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Blackjack\Model;

use Ecourty\PHPCasino\Blackjack\Exception\InvalidShoeException;

final readonly class GameRules
{
    public function __construct(
        public int $deckCount = 6,
        public bool $dealerHitsOnSoft17 = false,
        public float $blackjackPayout = 1.5,
        public bool $doubleAfterSplitAllowed = true,
        public bool $surrenderAllowed = true,
        public bool $insuranceAllowed = true,
        public float $shoePenetration = 0.75,
    ) {
        $this->validate();
    }

    public static function standard(): self
    {
        return new self();
    }

    public static function european(): self
    {
        return new self(
            deckCount: 6,
            dealerHitsOnSoft17: false,
            blackjackPayout: 1.5,
            doubleAfterSplitAllowed: false,
            surrenderAllowed: false,
            insuranceAllowed: true,
            shoePenetration: 0.75,
        );
    }

    public static function vegas(): self
    {
        return new self(
            deckCount: 6,
            dealerHitsOnSoft17: true,
            blackjackPayout: 1.5,
            doubleAfterSplitAllowed: true,
            surrenderAllowed: true,
            insuranceAllowed: true,
            shoePenetration: 0.75,
        );
    }

    public static function singleDeck(): self
    {
        return new self(
            deckCount: 1,
            dealerHitsOnSoft17: false,
            blackjackPayout: 1.5,
            doubleAfterSplitAllowed: false,
            surrenderAllowed: false,
            insuranceAllowed: true,
            shoePenetration: 0.5,
        );
    }

    private function validate(): void
    {
        if ($this->deckCount < 1 || $this->deckCount > 8) {
            throw InvalidShoeException::invalidDeckCount($this->deckCount);
        }

        if ($this->shoePenetration < 0.0 || $this->shoePenetration > 1.0) {
            throw InvalidShoeException::invalidPenetration($this->shoePenetration);
        }

        if ($this->blackjackPayout <= 0) {
            throw new \InvalidArgumentException("Blackjack payout must be positive, got {$this->blackjackPayout}");
        }
    }

    public function getDealerStandThreshold(): int
    {
        return 17;
    }
}
