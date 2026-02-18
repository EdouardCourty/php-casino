<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Blackjack\Enum;

enum ProbabilityCalculationMethod: string
{
    case ENUMERATION = 'enumeration';
    case MONTE_CARLO = 'monte_carlo';

    public function getName(): string
    {
        return match ($this) {
            self::ENUMERATION => 'Enumeration',
            self::MONTE_CARLO => 'Monte Carlo',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::ENUMERATION => 'Complete enumeration of all possible dealer hole cards. Perfect accuracy but slower with many decks.',
            self::MONTE_CARLO => 'Random sampling of possible outcomes. Very fast, accuracy improves with more iterations.',
        };
    }
}
