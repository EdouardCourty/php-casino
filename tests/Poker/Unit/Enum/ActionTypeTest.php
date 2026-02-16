<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Tests\Poker\Unit;

use Ecourty\PHPCasino\Poker\Enum\ActionType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ActionType::class)]
class ActionTypeTest extends TestCase
{
    /**
     */
    #[DataProvider('requiresAmountProvider')]
    public function testRequiresAmount(ActionType $actionType, bool $expected): void
    {
        $this->assertSame($expected, $actionType->requiresAmount());
    }

    /**
     * @return array<string, array{0: ActionType, 1: bool}>
     */
    public static function requiresAmountProvider(): array
    {
        return [
            'FOLD does not require amount' => [ActionType::FOLD, false],
            'CHECK does not require amount' => [ActionType::CHECK, false],
            'CALL does not require amount' => [ActionType::CALL, false],
            'BET requires amount' => [ActionType::BET, true],
            'RAISE requires amount' => [ActionType::RAISE, true],
            'ALL_IN requires amount' => [ActionType::ALL_IN, true],
        ];
    }

    /**
     */
    #[DataProvider('allowsAmountProvider')]
    public function testAllowsAmount(ActionType $actionType, bool $expected): void
    {
        $this->assertSame($expected, $actionType->allowsAmount());
    }

    /**
     * @return array<string, array{0: ActionType, 1: bool}>
     */
    public static function allowsAmountProvider(): array
    {
        return [
            'FOLD does not allow amount' => [ActionType::FOLD, false],
            'CHECK does not allow amount' => [ActionType::CHECK, false],
            'CALL allows amount' => [ActionType::CALL, true],
            'BET allows amount' => [ActionType::BET, true],
            'RAISE allows amount' => [ActionType::RAISE, true],
            'ALL_IN allows amount' => [ActionType::ALL_IN, true],
        ];
    }

    /**
     */
    #[DataProvider('isAggressiveProvider')]
    public function testIsAggressive(ActionType $actionType, bool $expected): void
    {
        $this->assertSame($expected, $actionType->isAggressive());
    }

    /**
     * @return array<string, array{0: ActionType, 1: bool}>
     */
    public static function isAggressiveProvider(): array
    {
        return [
            'FOLD is not aggressive' => [ActionType::FOLD, false],
            'CHECK is not aggressive' => [ActionType::CHECK, false],
            'CALL is not aggressive' => [ActionType::CALL, false],
            'BET is aggressive' => [ActionType::BET, true],
            'RAISE is aggressive' => [ActionType::RAISE, true],
            'ALL_IN is aggressive' => [ActionType::ALL_IN, true],
        ];
    }

    /**
     */
    #[DataProvider('isPassiveProvider')]
    public function testIsPassive(ActionType $actionType, bool $expected): void
    {
        $this->assertSame($expected, $actionType->isPassive());
    }

    /**
     * @return array<string, array{0: ActionType, 1: bool}>
     */
    public static function isPassiveProvider(): array
    {
        return [
            'FOLD is not passive' => [ActionType::FOLD, false],
            'CHECK is passive' => [ActionType::CHECK, true],
            'CALL is passive' => [ActionType::CALL, true],
            'BET is not passive' => [ActionType::BET, false],
            'RAISE is not passive' => [ActionType::RAISE, false],
            'ALL_IN is not passive' => [ActionType::ALL_IN, false],
        ];
    }

    /**
     */
    #[DataProvider('endsParticipationProvider')]
    public function testEndsParticipation(ActionType $actionType, bool $expected): void
    {
        $this->assertSame($expected, $actionType->endsParticipation());
    }

    /**
     * @return array<string, array{0: ActionType, 1: bool}>
     */
    public static function endsParticipationProvider(): array
    {
        return [
            'FOLD ends participation' => [ActionType::FOLD, true],
            'CHECK does not end participation' => [ActionType::CHECK, false],
            'CALL does not end participation' => [ActionType::CALL, false],
            'BET does not end participation' => [ActionType::BET, false],
            'RAISE does not end participation' => [ActionType::RAISE, false],
            'ALL_IN does not end participation' => [ActionType::ALL_IN, false],
        ];
    }

    /**
     */
    #[DataProvider('putsMoneyInPotProvider')]
    public function testPutsMoneyInPot(ActionType $actionType, bool $expected): void
    {
        $this->assertSame($expected, $actionType->putsMoneyInPot());
    }

    /**
     * @return array<string, array{0: ActionType, 1: bool}>
     */
    public static function putsMoneyInPotProvider(): array
    {
        return [
            'FOLD does not put money in pot' => [ActionType::FOLD, false],
            'CHECK does not put money in pot' => [ActionType::CHECK, false],
            'CALL puts money in pot' => [ActionType::CALL, true],
            'BET puts money in pot' => [ActionType::BET, true],
            'RAISE puts money in pot' => [ActionType::RAISE, true],
            'ALL_IN puts money in pot' => [ActionType::ALL_IN, true],
        ];
    }

    public function testAllActionTypesHaveUniqueValues(): void
    {
        $values = array_map(
            static fn (ActionType $type): string => $type->value,
            ActionType::cases(),
        );

        $this->assertCount(\count(ActionType::cases()), array_unique($values), 'All action type values should be unique');
    }

    public function testActionTypesMutualExclusivity(): void
    {
        // An action cannot be both aggressive and passive
        foreach (ActionType::cases() as $actionType) {
            if ($actionType->isAggressive()) {
                $this->assertFalse(
                    $actionType->isPassive(),
                    \sprintf('%s cannot be both aggressive and passive', $actionType->value),
                );
            }
        }
    }
}
