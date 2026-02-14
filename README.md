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

### Coming Soon

- **Roulette Module**: Wheel mechanics, betting systems, payout calculations
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
│   ├── Roulette/         # Coming soon
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
./vendor/bin/phpunit

# Run static analysis
./vendor/bin/phpstan analyse

# Run specific test suite
./vendor/bin/phpunit tests/Poker/Unit/
```

## Testing

The library is thoroughly tested with unit, integration, and functional tests:

- **Unit Tests**: Test individual classes in isolation
- **Integration Tests**: Test interactions between components
- **Functional Tests**: Test complete workflows and use cases

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
