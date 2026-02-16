# PHP Casino

A comprehensive PHP library for simulating casino games including Poker, Roulette, and Blackjack. Build game systems, test strategies, and implement casino game logic with ease.

## Table of Contents

- [Quick Start](#quick-start)
- [Installation](#installation)
- [Core Features](#core-features)
- [Usage](#usage)
  - [Poker](#poker)
    - [Basic Card Operations](#basic-card-operations)
    - [Deck Management](#deck-management)
    - [Board Management](#board-management)
    - [Hand Evaluation](#hand-evaluation)
    - [Equity Calculation](#equity-calculation)
  - [Roulette](#roulette)
    - [Board and Spinning](#board-and-spinning)
    - [Placing Bets](#placing-bets)
    - [Bet Validation](#bet-validation)
    - [Calculating Odds & Probabilities](#calculating-odds--probabilities)
    - [Complete Game Flow](#complete-game-flow)
- [Development](#development)
- [Testing](#testing)
- [License](#license)

## Quick Start

```php
use Ecourty\PHPCasino\Poker\Model\Board;
use Ecourty\PHPCasino\Poker\Service\HandEvaluator;

// Create a new poker board and advance through streets
$board = Board::createAtPreflop();
$board->advanceToNextStreet(); // Flop (burns 1, draws 3)
$board->advanceToNextStreet(); // Turn (burns 1, draws 1)
$board->advanceToNextStreet(); // River (burns 1, draws 1)

// Analyze board texture
if ($board->isSuited()) {
    echo "Monotone board!\n";
}

if ($board->hasFlushDraw()) {
    echo "Flush draw possible!\n";
}

// Evaluate a poker hand
$evaluator = new HandEvaluator();
$hand = $evaluator->evaluate($playerCards, $board->getCommunityCards());
echo $hand->getDetailedDescription(); // e.g., "Full House (Aces over Kings)"
```

## Installation

Install via Composer:

```bash
composer require ecourty/php-casino
```

**Requirements:**
- PHP 8.4 or higher
- No additional dependencies (standalone library)

## Core Features

### Poker Module

- **Card & Deck Management**: Immutable Card objects, mutable Deck with shuffle, draw, and burn operations
- **Board Management**: Automatic card dealing with poker burn rules, street progression (preflop → flop → turn → river → showdown)
- **Board Texture Analysis**: isSuited(), isPaired(), hasFlushDraw(), hasStraightDraw(), isRainbow(), suit/rank distributions
- **Hand Evaluation**: Evaluate best 5-card hand from any combination of hole cards and community cards
- **Equity Calculator**: Calculate winning probabilities using enumeration or Monte Carlo simulation
- **Player Ranges**: Define and work with hand ranges
- **Type Safety**: Fully typed with enums for ranks, suits, streets, hand ranks, and actions

### Roulette Module

- **Board & Wheel**: Immutable Board class with European (single zero) and American (double zero) support
- **Number Properties**: Complete RouletteNumber enum with 38 cases (0, 00, 1-36) and utility methods (isRed, isBlack, isEven, isOdd, isLow, isHigh, getDozen, getColumn)
- **Spinning**: Cryptographically secure random number generation via spin() method
- **Comprehensive Betting System**: Support for all 14 bet types
  - Inside bets: Straight Up, Split, Street, Corner, Five-Number, Line
  - Outside bets: Red, Black, Even, Odd, Low, High, Dozen, Column
- **Automatic Calculations**: Win/loss determination and payout calculations with correct odds
- **Odds Calculator**: Calculate win probabilities, expected value, house edge, and detailed statistics for any bet
- **Type Safety**: Fully typed with enums for bet types, roulette types, and numbers

### Coming Soon

- **Blackjack Module**: Card dealing, hand evaluation, dealer rules, player actions

## Usage

### Poker

#### Basic Card Operations

```php
use Ecourty\PHPCasino\Poker\Model\Card;
use Ecourty\PHPCasino\Poker\Enum\CardRank;
use Ecourty\PHPCasino\Poker\Enum\CardSuit;

// Create cards using enums
$aceOfSpades = new Card(CardRank::ACE, CardSuit::SPADES);

// Create cards from string notation
$kingOfHearts = Card::fromString('Kh');
$tenOfDiamonds = Card::fromString('10d');

// Get card information
echo $aceOfSpades->toString(); // "As"
echo $aceOfSpades->getValue(); // 14 (Ace high)
echo $aceOfSpades->getName();  // "Ace"
```

#### Deck Management

```php
use Ecourty\PHPCasino\Poker\Model\Deck;

// Create and shuffle a deck (two ways)
$deck = Deck::shuffled(); // Recommended: static helper
$deck = (new Deck())->shuffle(); // Alternative: method chaining

// Draw cards
$cards = $deck->draw(5); // Draw 5 cards
echo $deck->count(); // 47 cards remaining

// Burn cards (poker rules)
$deck->burn(1); // Burn 1 card before dealing
$flopCards = $deck->draw(3);

// Remove specific cards
$deck = new Deck();
$deck->removeCards([Card::fromString('Ah'), Card::fromString('Kh')]);
echo $deck->count(); // 50 cards remaining

// Reset deck
$deck->reset(); // Back to full 52-card deck
```

#### Board Management

The Board class manages poker community cards with automatic card dealing following official poker burn rules.

```php
use Ecourty\PHPCasino\Poker\Model\Board;
use Ecourty\PHPCasino\Poker\Enum\Street;

// Create a board at preflop (auto-creates and shuffles a deck)
$board = Board::createAtPreflop();

// Advance through streets (auto burns and draws)
$board->advanceToNextStreet(); // PREFLOP → FLOP (burn 1, draw 3)
$board->advanceToNextStreet(); // FLOP → TURN (burn 1, draw 1)
$board->advanceToNextStreet(); // TURN → RIVER (burn 1, draw 1)
$board->advanceToNextStreet(); // RIVER → SHOWDOWN (no burn/draw)

// Get board information
$street = $board->getCurrentStreet(); // Street::RIVER
$cards = $board->getCommunityCards(); // Array of 5 Card objects
$count = $board->getCardCount(); // 5
$deckRemaining = $board->getRemainingDeckCount(); // 44 cards

// Create board at specific street with known cards
$deck = new Deck();
$flopCards = [
    Card::fromString('Ah'),
    Card::fromString('Kh'),
    Card::fromString('Qh'),
];
$deck->removeCards($flopCards); // Deck must not contain community cards
$board = Board::createAtFlop($flopCards, $deck);

// Create from string notation
$board = Board::fromString(Street::FLOP, 'Ah Kd Qs', $deck);

// Analyze board texture
if ($board->isSuited()) {
    echo "All cards same suit (monotone)!\n";
}

if ($board->isRainbow()) {
    echo "All cards different suits (rainbow flop)!\n";
}

if ($board->isPaired()) {
    echo "Board has a pair!\n";
}

if ($board->hasFlushDraw()) {
    echo "3+ cards of same suit - flush draw possible!\n";
}

if ($board->hasStraightDraw()) {
    echo "Card spread allows straight possibilities!\n";
}

// Get distributions
$suitDist = $board->getSuitDistribution(); // ['h' => 3] for monotone hearts
$rankDist = $board->getRankDistribution(); // ['A' => 2, 'K' => 1] for AA-K board

// Reset board
$board->reset(); // Back to preflop, new shuffled deck
```

#### Hand Evaluation

```php
use Ecourty\PHPCasino\Poker\Service\HandEvaluator;
use Ecourty\PHPCasino\Poker\Model\Card;

$evaluator = new HandEvaluator();

// Player hole cards
$holeCards = [
    Card::fromString('Ah'),
    Card::fromString('Kh'),
];

// Board community cards
$communityCards = [
    Card::fromString('Qh'),
    Card::fromString('Jh'),
    Card::fromString('10h'),
];

// Evaluate hand
$hand = $evaluator->evaluate($holeCards, $communityCards);

echo $hand->getDescription(); // "Straight Flush"
echo $hand->getDetailedDescription(); // "Straight Flush (Ace high)"
echo $hand->getRankValue(); // 9 (hand rank value)

// Get hand as array
$handArray = $hand->toArray();
// [
//   'rank' => 9,
//   'kickers' => [14],
//   'cards' => ['Ah', 'Kh', 'Qh', 'Jh', '10h'],
//   'description' => 'Straight Flush',
//   'detailed_description' => 'Straight Flush (Ace high)'
// ]
```

#### Equity Calculation

Calculate winning probabilities for hands:

```php
use Ecourty\PHPCasino\Poker\Service\EquityCalculator;
use Ecourty\PHPCasino\Poker\Enum\EquityCalculationMethod;

$calculator = new EquityCalculator();

// Player 1 hole cards
$player1 = [Card::fromString('Ah'), Card::fromString('Kh')];

// Player 2 hole cards
$player2 = [Card::fromString('Qs'), Card::fromString('Jd')];

// Board (can be empty, partial, or complete)
$board = [Card::fromString('10h'), Card::fromString('9h')];

// Calculate equity using enumeration (exact)
$result = $calculator->calculate(
    [$player1, $player2],
    $board,
    EquityCalculationMethod::ENUMERATION
);

echo "Player 1 equity: " . ($result->getEquities()[0] * 100) . "%\n";
echo "Player 2 equity: " . ($result->getEquities()[1] * 100) . "%\n";
echo "Tie probability: " . ($result->getTieRate() * 100) . "%\n";

// Use Monte Carlo for faster approximation (large number of opponents)
$result = $calculator->calculate(
    [$player1, $player2, $player3, $player4],
    $board,
    EquityCalculationMethod::MONTE_CARLO,
    iterations: 10000
);
```

### Roulette

#### Board and Spinning

```php
use Ecourty\PHPCasino\Roulette\Model\Board;
use Ecourty\PHPCasino\Roulette\Enum\RouletteType;
use Ecourty\PHPCasino\Roulette\Enum\RouletteNumber;

// Create a European roulette board (single zero)
$board = Board::createEuropean();

// Create an American roulette board (double zero)
$board = Board::createAmerican();

// Spin the wheel (returns new Board instance with result)
// Spin the wheel (returns the result directly)
$result = $board->spin();

echo "Landed on: " . $result->name . "\n";

// Check number properties
if ($result->isRed()) {
    echo "Red number!\n";
}

if ($result->isEven()) {
    echo "Even number!\n";
}

if ($result->isLow()) {
    echo "Low number (1-18)\n";
}

$dozen = $result->getDozen(); // 1, 2, 3, or null for zeros
$column = $result->getColumn(); // 1, 2, 3, or null for zeros

// Get available numbers for the board type
$numbers = $board->getAvailableNumbers(); // 37 for European, 38 for American
```

#### Placing Bets

```php
use Ecourty\PHPCasino\Roulette\Model\Bet;
use Ecourty\PHPCasino\Roulette\Enum\BetType;

// Inside bets
$straightUp = Bet::straightUp(RouletteNumber::SEVEN, 10.0);
$split = Bet::split([RouletteNumber::ONE, RouletteNumber::TWO], 20.0);
$street = Bet::street([RouletteNumber::ONE, RouletteNumber::TWO, RouletteNumber::THREE], 15.0);
$corner = Bet::corner([RouletteNumber::ONE, RouletteNumber::TWO, RouletteNumber::FOUR, RouletteNumber::FIVE], 25.0);
$line = Bet::line([RouletteNumber::ONE, RouletteNumber::TWO, RouletteNumber::THREE, RouletteNumber::FOUR, RouletteNumber::FIVE, RouletteNumber::SIX], 30.0);

// Five-number bet (0, 00, 1, 2, 3 - American only)
$fiveNumber = Bet::fiveNumber(10.0);

// Outside bets
$red = Bet::red(50.0);
$black = Bet::black(50.0);
$even = Bet::even(50.0);
$odd = Bet::odd(50.0);
$low = Bet::low(50.0);  // 1-18
$high = Bet::high(50.0); // 19-36

// Dozen and column bets (position 1, 2, or 3)
$firstDozen = Bet::dozen(1, 30.0);  // 1-12
$secondDozen = Bet::dozen(2, 30.0); // 13-24
$thirdDozen = Bet::dozen(3, 30.0);  // 25-36

$firstColumn = Bet::column(1, 30.0);
$secondColumn = Bet::column(2, 30.0);
$thirdColumn = Bet::column(3, 30.0);

// Check if bet wins
if ($straightUp->isWinning(RouletteNumber::SEVEN)) {
    echo "Winner!\n";
}

// Calculate payout (includes original stake)
$payout = $straightUp->calculatePayout(RouletteNumber::SEVEN); // 360.0 (10 * 36)

// Calculate profit only (excludes stake)
$profit = $straightUp->calculateProfit(RouletteNumber::SEVEN); // 350.0 (10 * 35)

// Bet information
$type = $straightUp->getType();        // BetType::STRAIGHT_UP
$amount = $straightUp->getAmount();    // 10.0
$numbers = $straightUp->getNumbers();  // [RouletteNumber::SEVEN]
$payoutRatio = $type->getPayout();     // 35
```

#### Bet Validation

The library enforces strict validation rules for bet number combinations based on actual roulette table layout:

```php
use Ecourty\PHPCasino\Roulette\Model\Bet;
use Ecourty\PHPCasino\Roulette\Exception\InvalidBetException;

// Valid split: numbers must be adjacent (horizontally or vertically)
$validSplit = Bet::split([RouletteNumber::ONE, RouletteNumber::TWO], 10.0); // ✓ Adjacent horizontally
$validSplit = Bet::split([RouletteNumber::ONE, RouletteNumber::FOUR], 10.0); // ✓ Adjacent vertically

try {
    $invalidSplit = Bet::split([RouletteNumber::ONE, RouletteNumber::THIRTY_SIX], 10.0); // ✗ Not adjacent
} catch (InvalidBetException $e) {
    echo "Invalid split: " . $e->getMessage();
}

// Valid street: 3 consecutive numbers in same row
$validStreet = Bet::street([RouletteNumber::SEVEN, RouletteNumber::EIGHT, RouletteNumber::NINE], 10.0); // ✓

try {
    $invalidStreet = Bet::street([RouletteNumber::SEVEN, RouletteNumber::FOURTEEN, RouletteNumber::TWENTY_ONE], 10.0); // ✗ Different rows
} catch (InvalidBetException $e) {
    echo "Invalid street: " . $e->getMessage();
}

// Valid corner: 4 numbers forming a 2x2 square
$validCorner = Bet::corner([RouletteNumber::ONE, RouletteNumber::TWO, RouletteNumber::FOUR, RouletteNumber::FIVE], 10.0); // ✓

// Valid line: 6 consecutive numbers (two adjacent streets)
$validLine = Bet::line([RouletteNumber::ONE, RouletteNumber::TWO, RouletteNumber::THREE, RouletteNumber::FOUR, RouletteNumber::FIVE, RouletteNumber::SIX], 10.0); // ✓

// Numbers can be provided in any order (they're sorted automatically)
$street = Bet::street([RouletteNumber::NINE, RouletteNumber::SEVEN, RouletteNumber::EIGHT], 10.0); // ✓ Order doesn't matter
```

#### Calculating Odds & Probabilities

The OddsCalculator service provides detailed statistics for any bet, including win probability, expected value, and house edge:

```php
use Ecourty\PHPCasino\Roulette\Service\OddsCalculator;
use Ecourty\PHPCasino\Roulette\Enum\RouletteType;

$calculator = new OddsCalculator();

// Create a bet
$bet = Bet::red(10.0);

// Calculate odds for European roulette
$odds = $calculator->calculate($bet, RouletteType::EUROPEAN);

echo "Win Probability: " . $odds->getWinPercentage() . "%\n";        // 48.65%
echo "Expected Value: $" . $odds->expectedValue . "\n";              // -$0.27
echo "House Edge: " . $odds->getHouseEdgePercentage() . "%\n";       // 2.7%
echo "Winning Numbers: " . $odds->winningNumbersCount . "/37\n";     // 18/37

// Get payout details
echo "If you win: $" . $odds->getWinPayout() . " (profit: $" . $odds->getWinProfit() . ")\n";  // $20 (profit: $10)

// Compare European vs American roulette
$oddsAmerican = $calculator->calculate($bet, RouletteType::AMERICAN);

echo "\nAmerican Roulette:\n";
echo "Win Probability: " . $oddsAmerican->getWinPercentage() . "%\n";   // 47.37%
echo "Expected Value: $" . $oddsAmerican->expectedValue . "\n";         // -$0.53
echo "House Edge: " . $oddsAmerican->getHouseEdgePercentage() . "%\n";  // 5.26%

// Calculate odds for different bet types
$straightUpOdds = $calculator->calculate(
    Bet::straightUp(RouletteNumber::SEVEN, 100.0),
    RouletteType::EUROPEAN
);

echo "\nStraight Up (€100 bet):\n";
echo "Win Chance: " . $straightUpOdds->getWinPercentage() . "%\n";     // 2.70%
echo "Win Payout: €" . $straightUpOdds->getWinPayout() . "\n";         // €3,600
echo "Win Profit: €" . $straightUpOdds->getWinProfit() . "\n";         // €3,500
echo "Expected Loss: €" . abs($straightUpOdds->expectedValue) . "\n";  // €2.70

// Analyze the WORST bet in roulette (five-number on American wheel)
$fiveNumberOdds = $calculator->calculate(
    Bet::fiveNumber(10.0),
    RouletteType::AMERICAN
);

echo "\nFive-Number Bet (The Worst Bet!):\n";
echo "House Edge: " . $fiveNumberOdds->getHouseEdgePercentage() . "%\n";  // 7.89% (vs 5.26% standard)
echo "Expected Value: $" . $fiveNumberOdds->expectedValue . "\n";         // -$0.79

// Export odds data
$data = $odds->toPercentageArray();
/*
[
    'win_pct' => 48.65,
    'loss_pct' => 51.35,
    'expected_value' => -0.27,
    'expected_profit_pct' => -2.7,
    'house_edge_pct' => 2.7,
    'bet_amount' => 10.0,
    'winning_numbers' => 18,
    'total_numbers' => 37,
    'payout_ratio' => 1,
    'win_payout' => 20.0,
    'win_profit' => 10.0
]
*/
```

**Key Insights:**

- **European Roulette**: 2.7% house edge (37 numbers with single zero)
- **American Roulette**: 5.26% house edge (38 numbers with double zero)
- **Five-Number Bet**: 7.89% house edge (worst bet in roulette!)
- All bets in European roulette have the same 2.7% house edge
- All bets in American roulette have 5.26% house edge (except five-number at 7.89%)
- Expected value is always negative, representing the long-term cost per bet

#### Complete Game Flow

```php
use Ecourty\PHPCasino\Roulette\Model\Board;
use Ecourty\PHPCasino\Roulette\Model\Bet;
use Ecourty\PHPCasino\Roulette\Enum\RouletteNumber;

// Create European board
$board = Board::createEuropean();

// Place multiple bets
$bets = [
    Bet::straightUp(RouletteNumber::SEVEN, 10.0),
    Bet::red(50.0),
    Bet::dozen(1, 30.0),
    Bet::column(1, 20.0),
];

// Spin the wheel
$result = $board->spin();

echo "Result: " . $result->name . "\n\n";

// Calculate winnings for each bet
$totalPayout = 0.0;
$totalStaked = 0.0;

foreach ($bets as $bet) {
    $totalStaked += $bet->getAmount();
    
    if ($bet->isWinning($result)) {
        $payout = $bet->calculatePayout($result);
        $profit = $bet->calculateProfit($result);
        
        echo "{$bet->getType()->name} bet: WON! Payout: ${payout} (Profit: ${profit})\n";
        $totalPayout += $payout;
    } else {
        echo "{$bet->getType()->name} bet: Lost\n";
    }
}

$netResult = $totalPayout - $totalStaked;
echo "\nTotal staked: ${totalStaked}\n";
echo "Total payout: ${totalPayout}\n";
echo "Net result: " . ($netResult >= 0 ? "+$netResult" : "$netResult") . "\n";
```

#### Payout Ratios

| Bet Type | Payout Ratio | Example (€10 bet) |
|----------|--------------|-------------------|
| Straight Up | 35:1 | €360 total (€350 profit) |
| Split | 17:1 | €180 total (€170 profit) |
| Street | 11:1 | €120 total (€110 profit) |
| Corner | 8:1 | €90 total (€80 profit) |
| Five-Number | 6:1 | €70 total (€60 profit) |
| Line | 5:1 | €60 total (€50 profit) |
| Dozen | 2:1 | €30 total (€20 profit) |
| Column | 2:1 | €30 total (€20 profit) |
| Red/Black/Even/Odd/Low/High | 1:1 | €20 total (€10 profit) |

## Development

### Project Structure

```
php-casino/
├── src/
│   ├── Poker/
│   │   ├── Enum/         # Type-safe enumerations
│   │   ├── Model/        # Domain entities (Card, Deck, Board, Hand, etc.)
│   │   ├── Service/      # Business logic (HandEvaluator, EquityCalculator)
│   │   └── Exception/    # Domain exceptions
│   ├── Roulette/
│   │   ├── Enum/         # RouletteNumber, RouletteType, BetType
│   │   ├── Model/        # Board, Bet
│   │   └── Exception/    # Domain exceptions
│   └── Blackjack/        # Coming soon
└── tests/
    ├── Unit/
    ├── Integration/
    └── Functional/
```

### Coding Standards

- **PSR-4** autoloading
- **PSR-12** coding style
- **Strict types** everywhere (`declare(strict_types=1)`)
- **Immutable value objects** where appropriate (Card, Hand)
- **Type safety** with PHP 8.4 enums and property types

### Building & Testing

```bash
# Install dependencies
composer install

# Run tests
composer test
# or
./vendor/bin/phpunit

# Run static analysis (PHPStan level max)
composer phpstan
# or
./vendor/bin/phpstan analyse

# Check code style (PSR-12)
composer cs-check

# Fix code style
composer cs-fix

# Run specific test suite
./vendor/bin/phpunit tests/Poker/Unit/
./vendor/bin/phpunit tests/Roulette/Unit/
```

## Testing

The library is thoroughly tested with unit, integration, and functional tests:

- **Unit Tests**: Test individual classes in isolation
- **Integration Tests**: Test interactions between components
- **Functional Tests**: Test complete workflows and use cases

Test coverage includes:
- **Poker Module**: Board management, hand evaluation, equity calculation
- **Roulette Module**: Number properties, betting system, spin mechanics, odds calculation (194 tests, 664 assertions)

Run the full test suite:

```bash
composer test
```

## Contributing

Contributions are welcome! Please ensure:

1. All tests pass
2. PHPStan analysis passes at max level
3. Code follows PSR-12 style guidelines
4. New features include comprehensive tests
5. Documentation is updated

## License

MIT License - see LICENSE file for details

---

**Author**: Emmanuel Courty  
**Package**: `ecourty/php-casino`  
**PHP Version**: 8.4+
