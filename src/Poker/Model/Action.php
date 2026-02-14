<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Poker\Model;

use Ecourty\PHPCasino\Poker\Enum\ActionType;
use Ecourty\PHPCasino\Poker\Exception\InvalidActionException;

/**
 * Represents a poker action taken by a player.
 * Immutable value object that encapsulates action type and optional amount.
 */
final readonly class Action
{
    /**
     * @throws InvalidActionException If amount validation fails
     */
    public function __construct(
        public ActionType $type,
        public int $amount = 0,
    ) {
        $this->validate();
    }

    /**
     * Creates a FOLD action.
     */
    public static function fold(): self
    {
        return new self(ActionType::FOLD);
    }

    /**
     * Creates a CHECK action.
     */
    public static function check(): self
    {
        return new self(ActionType::CHECK);
    }

    /**
     * Creates a CALL action.
     *
     * @param int $amount Optional amount being called (for clarity/logging)
     */
    public static function call(int $amount = 0): self
    {
        return new self(ActionType::CALL, $amount);
    }

    /**
     * Creates a BET action.
     *
     * @throws InvalidActionException If amount is not positive
     */
    public static function bet(int $amount): self
    {
        return new self(ActionType::BET, $amount);
    }

    /**
     * Creates a RAISE action.
     *
     * @throws InvalidActionException If amount is not positive
     */
    public static function raise(int $amount): self
    {
        return new self(ActionType::RAISE, $amount);
    }

    /**
     * Creates an ALL_IN action.
     *
     * @throws InvalidActionException If amount is not positive
     */
    public static function allIn(int $amount): self
    {
        return new self(ActionType::ALL_IN, $amount);
    }

    /**
     * Returns true if this is a FOLD action.
     */
    public function isFold(): bool
    {
        return $this->type === ActionType::FOLD;
    }

    /**
     * Returns true if this is an aggressive action (bet/raise/all-in).
     */
    public function isAggressive(): bool
    {
        return $this->type->isAggressive();
    }

    /**
     * Returns true if this is a passive action (check/call).
     */
    public function isPassive(): bool
    {
        return $this->type->isPassive();
    }

    /**
     * Returns true if this action ends the player's participation.
     */
    public function endsParticipation(): bool
    {
        return $this->type->endsParticipation();
    }

    /**
     * Returns true if this action puts money in the pot.
     */
    public function putsMoneyInPot(): bool
    {
        return $this->type->putsMoneyInPot();
    }

    /**
     * Validates the action according to poker rules.
     *
     * @throws InvalidActionException If validation fails
     */
    private function validate(): void
    {
        // Actions that require an amount must have amount > 0
        if ($this->type->requiresAmount() && $this->amount <= 0) {
            throw new InvalidActionException(
                \sprintf(
                    'Action %s requires a positive amount, got: %d',
                    $this->type->value,
                    $this->amount,
                ),
            );
        }

        // Actions that don't allow amounts must have amount = 0
        if (!$this->type->allowsAmount() && $this->amount !== 0) {
            throw new InvalidActionException(
                \sprintf(
                    'Action %s does not allow an amount, got: %d',
                    $this->type->value,
                    $this->amount,
                ),
            );
        }

        // Amount cannot be negative
        if ($this->amount < 0) {
            throw new InvalidActionException(
                \sprintf(
                    'Action amount cannot be negative, got: %d',
                    $this->amount,
                ),
            );
        }
    }

    /**
     * Returns a string representation of the action.
     */
    public function toString(): string
    {
        if ($this->amount > 0) {
            return \sprintf('%s(%d)', $this->type->value, $this->amount);
        }

        return $this->type->value;
    }
}
