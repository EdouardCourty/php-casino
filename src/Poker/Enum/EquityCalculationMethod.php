<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Poker\Enum;

/**
 * Methods for calculating poker hand equity.
 */
enum EquityCalculationMethod: string
{
    /**
     * Monte Carlo simulation - fast but approximate.
     * Runs random simulations to estimate probabilities.
     */
    case MONTE_CARLO = 'monte_carlo';

    /**
     * Complete enumeration - exact but slow.
     * Evaluates all possible card combinations.
     */
    case ENUMERATION = 'enumeration';
}
