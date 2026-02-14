<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Tests\Poker\Unit;

use Ecourty\PHPCasino\Poker\Enum\ActionType;
use Ecourty\PHPCasino\Poker\Exception\InvalidActionException;
use Ecourty\PHPCasino\Poker\Model\Action;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Action::class)]
class ActionTest extends TestCase
{
    // ============ Factory Methods Tests ============

    public function testFoldFactory(): void
    {
        $action = Action::fold();

        $this->assertSame(ActionType::FOLD, $action->type);
        $this->assertSame(0, $action->amount);
    }

    public function testCheckFactory(): void
    {
        $action = Action::check();

        $this->assertSame(ActionType::CHECK, $action->type);
        $this->assertSame(0, $action->amount);
    }

    public function testCallFactory(): void
    {
        $action = Action::call();
        $this->assertSame(ActionType::CALL, $action->type);
        $this->assertSame(0, $action->amount);

        $actionWithAmount = Action::call(100);
        $this->assertSame(100, $actionWithAmount->amount);
    }

    public function testBetFactory(): void
    {
        $action = Action::bet(50);

        $this->assertSame(ActionType::BET, $action->type);
        $this->assertSame(50, $action->amount);
    }

    public function testRaiseFactory(): void
    {
        $action = Action::raise(100);

        $this->assertSame(ActionType::RAISE, $action->type);
        $this->assertSame(100, $action->amount);
    }

    public function testAllInFactory(): void
    {
        $action = Action::allIn(500);

        $this->assertSame(ActionType::ALL_IN, $action->type);
        $this->assertSame(500, $action->amount);
    }

    // ============ Validation Tests ============

    public function testFoldWithAmountThrowsException(): void
    {
        $this->expectException(InvalidActionException::class);
        $this->expectExceptionMessage('Action fold does not allow an amount');

        new Action(ActionType::FOLD, 100);
    }

    public function testCheckWithAmountThrowsException(): void
    {
        $this->expectException(InvalidActionException::class);
        $this->expectExceptionMessage('Action check does not allow an amount');

        new Action(ActionType::CHECK, 50);
    }

    public function testBetWithoutAmountThrowsException(): void
    {
        $this->expectException(InvalidActionException::class);
        $this->expectExceptionMessage('Action bet requires a positive amount');

        new Action(ActionType::BET, 0);
    }

    public function testRaiseWithoutAmountThrowsException(): void
    {
        $this->expectException(InvalidActionException::class);
        $this->expectExceptionMessage('Action raise requires a positive amount');

        new Action(ActionType::RAISE, 0);
    }

    public function testAllInWithoutAmountThrowsException(): void
    {
        $this->expectException(InvalidActionException::class);
        $this->expectExceptionMessage('Action all_in requires a positive amount');

        new Action(ActionType::ALL_IN, 0);
    }

    public function testBetWithNegativeAmountThrowsException(): void
    {
        $this->expectException(InvalidActionException::class);
        // Negative amount is caught before the "requires positive amount" check
        $this->expectExceptionMessage('Action bet requires a positive amount');

        new Action(ActionType::BET, -100);
    }

    public function testFoldWithNegativeAmountThrowsException(): void
    {
        $this->expectException(InvalidActionException::class);
        // FOLD doesn't allow amounts, so this is caught first
        $this->expectExceptionMessage('Action fold does not allow an amount');

        new Action(ActionType::FOLD, -1);
    }

    // ============ Helper Methods Tests ============

    /**
     */
    #[DataProvider('isFoldProvider')]
    public function testIsFold(Action $action, bool $expected): void
    {
        $this->assertSame($expected, $action->isFold());
    }

    /**
     * @return array<string, array{0: Action, 1: bool}>
     */
    public static function isFoldProvider(): array
    {
        return [
            'FOLD is fold' => [Action::fold(), true],
            'CHECK is not fold' => [Action::check(), false],
            'CALL is not fold' => [Action::call(), false],
            'BET is not fold' => [Action::bet(50), false],
            'RAISE is not fold' => [Action::raise(100), false],
            'ALL_IN is not fold' => [Action::allIn(200), false],
        ];
    }

    /**
     */
    #[DataProvider('isAggressiveProvider')]
    public function testIsAggressive(Action $action, bool $expected): void
    {
        $this->assertSame($expected, $action->isAggressive());
    }

    /**
     * @return array<string, array{0: Action, 1: bool}>
     */
    public static function isAggressiveProvider(): array
    {
        return [
            'FOLD is not aggressive' => [Action::fold(), false],
            'CHECK is not aggressive' => [Action::check(), false],
            'CALL is not aggressive' => [Action::call(), false],
            'BET is aggressive' => [Action::bet(50), true],
            'RAISE is aggressive' => [Action::raise(100), true],
            'ALL_IN is aggressive' => [Action::allIn(200), true],
        ];
    }

    /**
     */
    #[DataProvider('isPassiveProvider')]
    public function testIsPassive(Action $action, bool $expected): void
    {
        $this->assertSame($expected, $action->isPassive());
    }

    /**
     * @return array<string, array{0: Action, 1: bool}>
     */
    public static function isPassiveProvider(): array
    {
        return [
            'FOLD is not passive' => [Action::fold(), false],
            'CHECK is passive' => [Action::check(), true],
            'CALL is passive' => [Action::call(), true],
            'BET is not passive' => [Action::bet(50), false],
            'RAISE is not passive' => [Action::raise(100), false],
            'ALL_IN is not passive' => [Action::allIn(200), false],
        ];
    }

    /**
     */
    #[DataProvider('endsParticipationProvider')]
    public function testEndsParticipation(Action $action, bool $expected): void
    {
        $this->assertSame($expected, $action->endsParticipation());
    }

    /**
     * @return array<string, array{0: Action, 1: bool}>
     */
    public static function endsParticipationProvider(): array
    {
        return [
            'FOLD ends participation' => [Action::fold(), true],
            'CHECK does not end participation' => [Action::check(), false],
            'CALL does not end participation' => [Action::call(), false],
            'BET does not end participation' => [Action::bet(50), false],
        ];
    }

    /**
     */
    #[DataProvider('putsMoneyInPotProvider')]
    public function testPutsMoneyInPot(Action $action, bool $expected): void
    {
        $this->assertSame($expected, $action->putsMoneyInPot());
    }

    /**
     * @return array<string, array{0: Action, 1: bool}>
     */
    public static function putsMoneyInPotProvider(): array
    {
        return [
            'FOLD does not put money' => [Action::fold(), false],
            'CHECK does not put money' => [Action::check(), false],
            'CALL puts money' => [Action::call(50), true],
            'BET puts money' => [Action::bet(50), true],
            'RAISE puts money' => [Action::raise(100), true],
            'ALL_IN puts money' => [Action::allIn(200), true],
        ];
    }

    // ============ ToString Tests ============

    /**
     */
    #[DataProvider('toStringProvider')]
    public function testToString(Action $action, string $expected): void
    {
        $this->assertSame($expected, $action->toString());
    }

    /**
     * @return array<string, array{0: Action, 1: string}>
     */
    public static function toStringProvider(): array
    {
        return [
            'FOLD' => [Action::fold(), 'fold'],
            'CHECK' => [Action::check(), 'check'],
            'CALL without amount' => [Action::call(), 'call'],
            'CALL with amount' => [Action::call(50), 'call(50)'],
            'BET' => [Action::bet(100), 'bet(100)'],
            'RAISE' => [Action::raise(200), 'raise(200)'],
            'ALL_IN' => [Action::allIn(500), 'all_in(500)'],
        ];
    }

    // ============ Immutability Tests ============

    public function testActionIsImmutable(): void
    {
        $action = Action::bet(100);

        $this->assertSame(ActionType::BET, $action->type);
        $this->assertSame(100, $action->amount);

        // Cannot modify readonly properties
        $this->expectException(\Error::class);
        // @phpstan-ignore-next-line (testing readonly enforcement)
        $action->amount = 200;
    }

    // ============ Edge Cases ============

    public function testCallWithZeroAmount(): void
    {
        $action = Action::call(0);

        $this->assertSame(ActionType::CALL, $action->type);
        $this->assertSame(0, $action->amount);
        $this->assertSame('call', $action->toString());
    }

    public function testBetFactoryWithZeroThrowsException(): void
    {
        $this->expectException(InvalidActionException::class);
        $this->expectExceptionMessage('Action bet requires a positive amount');

        Action::bet(0);
    }

    public function testRaiseFactoryWithZeroThrowsException(): void
    {
        $this->expectException(InvalidActionException::class);
        $this->expectExceptionMessage('Action raise requires a positive amount');

        Action::raise(0);
    }

    public function testAllInFactoryWithZeroThrowsException(): void
    {
        $this->expectException(InvalidActionException::class);
        $this->expectExceptionMessage('Action all_in requires a positive amount');

        Action::allIn(0);
    }

    public function testLargeAmounts(): void
    {
        $largeAmount = 1000000;
        $action = Action::bet($largeAmount);

        $this->assertSame($largeAmount, $action->amount);
        $this->assertSame("bet($largeAmount)", $action->toString());
    }
}
