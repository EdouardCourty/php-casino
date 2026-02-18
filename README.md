# PHP Casino

A comprehensive PHP library for simulating casino games including Poker, Roulette, and Blackjack. Build game systems, test strategies, and implement casino game logic with ease.

## Table of Contents

- [Quick Start](#quick-start)
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
  - [Blackjack](#blackjack)
    - [Shoe Management](#shoe-management)
    - [Hand Evaluation](#hand-evaluation-1)
    - [Game Rules & Variations](#game-rules--variations)
    - [Probability Calculation](#probability-calculation)
    - [Complete Game Scenario](#complete-game-scenario)
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

### Blackjack Module

- **Shoe Management**: Multi-deck shoe (1-8 decks) with cut card and penetration support
- **Hand Evaluation**: Soft/hard hand detection, automatic Ace value calculation (1 or 11)
- **Game Rules**: Comprehensive rule system (dealer soft 17, double after split, surrender, insurance, blackjack payouts)
- **Probability Calculators**: Win/loss/push probabilities using exact enumeration or Monte Carlo simulation
- **Type Safety**: Fully typed with enums for actions, game results, and hand types

### Coming Soon

- **Blackjack Module**: Coming soon!

## Usage

### Poker

#### Basic Card Operations

```php
use Ecourty\PHPCasino\Common\Enum\CardRank;use Ecourty\PHPCasino\Common\Enum\CardSuit;use Ecourty\PHPCasino\Common\Model\Card;

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
use Ecourty\PHPCasino\Common\Model\Deck;

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
$street = $board->getStreet(); // Street::RIVER
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
use Ecourty\PHPCasino\Common\Model\Card;use Ecourty\PHPCasino\Poker\Service\HandEvaluator;

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

### Blackjack

#### Shoe Management

```php
use Ecourty\PHPCasino\Blackjack\Model\Shoe;
use Ecourty\PHPCasino\Blackjack\Model\GameRules;

// Create a 6-deck shoe with 75% penetration
$shoe = new Shoe(deckCount: 6, penetration: 0.75);

// Shuffle the shoe
$shoe->shuffle();

// Draw cards
$cards = $shoe->draw(2); // Deal 2 cards
echo $shoe->getCardCount(); // 310 cards remaining

// Check if reshuffle is needed (based on cut card position)
if ($shoe->needsReshuffle()) {
    $shoe->reshuffle(); // Reset and shuffle
}

// Create shoe from game rules
$rules = GameRules::standard();
$shoe = Shoe::fromGameRules($rules);

// Get shoe information
$deckCount = $shoe->getDeckCount();           // 6
$totalCards = $shoe->getTotalCardCount();     // 312
$remaining = $shoe->getCardCount();           // Current cards in shoe
$dealt = $shoe->getCardsDealt();              // Cards dealt since last shuffle
$cutPosition = $shoe->getCutCardPosition();   // 234 (75% of 312)
```

#### Hand Evaluation

The HandEvaluator service handles all hand value calculations, including soft/hard Ace logic:

```php
use Ecourty\PHPCasino\Blackjack\Model\Hand;
use Ecourty\PHPCasino\Blackjack\Service\HandEvaluator;
use Ecourty\PHPCasino\Common\Model\Card;
use Ecourty\PHPCasino\Common\Enum\CardRank;
use Ecourty\PHPCasino\Common\Enum\CardSuit;

$evaluator = new HandEvaluator();

// Create a hand
$hand = Hand::fromCards(
    new Card(CardRank::ACE, CardSuit::SPADES),
    new Card(CardRank::SIX, CardSuit::HEARTS)
);

// Get hand value (automatically handles Ace as 1 or 11)
echo $evaluator->getHandValue($hand); // 17 (Ace counted as 11)

// Check hand type
$type = $evaluator->getHandType($hand); // HandType::SOFT

// Check specific conditions
if ($evaluator->isSoft($hand)) {
    echo "Soft hand (Ace counted as 11)\n";
}

if ($evaluator->isBlackjack($hand)) {
    echo "Blackjack! (21 with 2 cards: Ace + 10-value)\n";
}

if ($evaluator->isBust($hand)) {
    echo "Bust (over 21)\n";
}

// Examples with different hands
$hardHand = Hand::fromCards(
    new Card(CardRank::ACE, CardSuit::SPADES),
    new Card(CardRank::KING, CardSuit::HEARTS),
    new Card(CardRank::FIVE, CardSuit::CLUBS)
);
echo $evaluator->getHandValue($hardHand); // 16 (Ace counted as 1)
echo $evaluator->isSoft($hardHand);       // false (hard hand)

$blackjack = Hand::fromCards(
    new Card(CardRank::ACE, CardSuit::SPADES),
    new Card(CardRank::KING, CardSuit::HEARTS)
);
echo $evaluator->isBlackjack($blackjack); // true
echo $evaluator->getHandValue($blackjack); // 21

// Compare player vs dealer hands
$playerHand = Hand::fromCards(
    new Card(CardRank::TEN, CardSuit::SPADES),
    new Card(CardRank::NINE, CardSuit::HEARTS)
);

$dealerHand = Hand::fromCards(
    new Card(CardRank::KING, CardSuit::CLUBS),
    new Card(CardRank::SEVEN, CardSuit::DIAMONDS)
);

$result = $evaluator->compare($playerHand, $dealerHand);
echo $result->getName(); // "Player Win"

// Check if dealer should hit
$rules = GameRules::standard();
if ($evaluator->shouldDealerHit($dealerHand, $rules)) {
    echo "Dealer must hit\n";
} else {
    echo "Dealer must stand\n";
}
```

#### Game Rules & Variations

Configure game rules to match different casino variations:

```php
use Ecourty\PHPCasino\Blackjack\Model\GameRules;

// Standard 6-deck rules
$standard = GameRules::standard();
echo $standard->deckCount;                 // 6
echo $standard->dealerHitsOnSoft17;        // false
echo $standard->blackjackPayout;           // 1.5 (3:2 payout)
echo $standard->doubleAfterSplitAllowed;   // true
echo $standard->surrenderAllowed;          // true
echo $standard->insuranceAllowed;          // true
echo $standard->shoePenetration;           // 0.75

// European Blackjack rules
$european = GameRules::european();
echo $european->doubleAfterSplitAllowed;   // false
echo $european->surrenderAllowed;          // false

// Vegas Strip rules
$vegas = GameRules::vegas();
echo $vegas->dealerHitsOnSoft17;          // true

// Single deck rules
$singleDeck = GameRules::singleDeck();
echo $singleDeck->deckCount;              // 1
echo $singleDeck->shoePenetration;        // 0.5

// Custom rules
$custom = new GameRules(
    deckCount: 8,
    dealerHitsOnSoft17: true,
    blackjackPayout: 1.2,              // 6:5 payout (less favorable)
    doubleAfterSplitAllowed: false,
    surrenderAllowed: false,
    insuranceAllowed: false,
    shoePenetration: 0.6,
);
```

#### Probability Calculation

Calculate win/loss/push probabilities using the unified ProbabilityCalculator with two methods:

**Using the main ProbabilityCalculator (recommended):**

```php
use Ecourty\PHPCasino\Blackjack\Service\ProbabilityCalculator;
use Ecourty\PHPCasino\Blackjack\Service\ProbabilityCalculator\ExactProbabilityCalculator;
use Ecourty\PHPCasino\Blackjack\Service\ProbabilityCalculator\MonteCarloProbabilityCalculator;
use Ecourty\PHPCasino\Blackjack\Enum\ProbabilityCalculationMethod;
use Ecourty\PHPCasino\Blackjack\Model\Hand;
use Ecourty\PHPCasino\Blackjack\Model\Shoe;
use Ecourty\PHPCasino\Blackjack\Model\GameRules;
use Ecourty\PHPCasino\Common\Model\Card;
use Ecourty\PHPCasino\Common\Enum\CardRank;
use Ecourty\PHPCasino\Common\Enum\CardSuit;

// Create the calculator (dependency injection)
$calculator = ProbabilityCalculator::create();

// Player's hand (standing at 19)
$playerHand = Hand::fromCards(
    new Card(CardRank::TEN, CardSuit::SPADES),
    new Card(CardRank::NINE, CardSuit::HEARTS)
);

// Dealer's visible card
$dealerUpCard = new Card(CardRank::SIX, CardSuit::CLUBS);

// Known cards (player's cards + dealer's up card)
$knownCards = [
    new Card(CardRank::TEN, CardSuit::SPADES),
    new Card(CardRank::NINE, CardSuit::HEARTS),
    new Card(CardRank::SIX, CardSuit::CLUBS),
];

$shoe = new Shoe(6);
$rules = GameRules::standard();

// Method 1: Enumeration (exact, best for 1-2 decks)
$result = $calculator->calculate(
    $playerHand,
    $dealerUpCard,
    $knownCards,
    $shoe,
    $rules,
    ProbabilityCalculationMethod::ENUMERATION
);

// Method 2: Monte Carlo (fast, best for 4-8 decks) - DEFAULT
$result = $calculator->calculate(
    $playerHand,
    $dealerUpCard,
    $knownCards,
    $shoe,
    $rules,
    ProbabilityCalculationMethod::MONTE_CARLO,
    iterations: 10000
);

// Or use default (Monte Carlo with 10,000 iterations)
$result = $calculator->calculate($playerHand, $dealerUpCard, $knownCards, $shoe, $rules);

echo "Win Probability: " . $result->getWinProbabilityPercent() . "%\n";
echo "Loss Probability: " . $result->getLossProbabilityPercent() . "%\n";
echo "Push Probability: " . $result->getPushProbabilityPercent() . "%\n";
echo "Expected Value: " . $result->expectedValue . "\n";
echo "Scenarios Analyzed: " . $result->scenariosConsidered . "\n";
```

**Or use calculators directly (for more control):**

**1. Exact Enumeration (Precise, best for 1-2 decks):**

```php
use Ecourty\PHPCasino\Blackjack\Model\GameRules;use Ecourty\PHPCasino\Blackjack\Model\Hand;use Ecourty\PHPCasino\Blackjack\Model\Shoe;use Ecourty\PHPCasino\Blackjack\Service\ProbabilityCalculator\ExactProbabilityCalculator;use Ecourty\PHPCasino\Common\Enum\CardRank;use Ecourty\PHPCasino\Common\Enum\CardSuit;use Ecourty\PHPCasino\Common\Model\Card;

$calculator = new ExactProbabilityCalculator();

// Player's hand (standing at 19)
$playerHand = Hand::fromCards(
    new Card(CardRank::TEN, CardSuit::SPADES),
    new Card(CardRank::NINE, CardSuit::HEARTS)
);

// Dealer's visible card
$dealerUpCard = new Card(CardRank::SIX, CardSuit::CLUBS);

// Known cards (player's cards + dealer's up card)
$knownCards = [
    new Card(CardRank::TEN, CardSuit::SPADES),
    new Card(CardRank::NINE, CardSuit::HEARTS),
    new Card(CardRank::SIX, CardSuit::CLUBS),
];

$shoe = new Shoe(1); // Single deck for exact calculation
$rules = GameRules::standard();

$result = $calculator->calculate($playerHand, $dealerUpCard, $knownCards, $shoe, $rules);

echo "Method: " . $result->method . "\n";                          // "exact"
echo "Win Probability: " . $result->getWinProbabilityPercent() . "%\n";
echo "Loss Probability: " . $result->getLossProbabilityPercent() . "%\n";
echo "Push Probability: " . $result->getPushProbabilityPercent() . "%\n";
echo "Expected Value: " . $result->expectedValue . "\n";           // Positive = player advantage
echo "Scenarios Analyzed: " . $result->scenariosConsidered . "\n"; // All possible dealer hole cards

if ($result->isPlayerFavored()) {
    echo "Player is favored to win!\n";
}
```

**2. Monte Carlo Simulation (Fast, best for 4-8 decks):**

```php
use Ecourty\PHPCasino\Blackjack\Service\ProbabilityCalculator\MonteCarloProbabilityCalculator;

$calculator = new MonteCarloProbabilityCalculator();

// Same setup as above
$playerHand = Hand::fromCards(
    new Card(CardRank::KING, CardSuit::SPADES),
    new Card(CardRank::TEN, CardSuit::HEARTS)
);

$dealerUpCard = new Card(CardRank::FIVE, CardSuit::CLUBS);

$knownCards = [
    new Card(CardRank::KING, CardSuit::SPADES),
    new Card(CardRank::TEN, CardSuit::HEARTS),
    new Card(CardRank::FIVE, CardSuit::CLUBS),
];

$shoe = new Shoe(6); // 6-deck shoe
$rules = GameRules::standard();

// Run 10,000 simulations (more iterations = more accurate)
$result = $calculator->calculate(
    $playerHand,
    $dealerUpCard,
    $knownCards,
    $shoe,
    $rules,
    iterations: 10000
);

echo "Method: " . $result->method . "\n";                          // "monte_carlo"
echo "Win Probability: " . $result->getWinProbabilityPercent() . "%\n";
echo "Loss Probability: " . $result->getLossProbabilityPercent() . "%\n";
echo "Push Probability: " . $result->getPushProbabilityPercent() . "%\n";
echo "Expected Value: " . $result->expectedValue . "\n";
echo "Simulations Run: " . $result->scenariosConsidered . "\n";    // 10,000

// Higher iterations for more precision (slower but more accurate)
$preciseResult = $calculator->calculate($playerHand, $dealerUpCard, $knownCards, $shoe, $rules, 100000);
```

**Key Insights:**

- **Main Calculator**: Use `ProbabilityCalculator` for clean API with method dispatch
- **Direct Calculators**: Use specific calculators for fine-grained control
- **Enumeration Calculator**: Enumerates all possible dealer hole cards. Perfect accuracy but slow with many decks.
- **Monte Carlo Calculator**: Randomly samples possible outcomes. Very fast, accuracy improves with more iterations.
- **When to use which**: 
  - 1-2 decks: Use Exact (fast enough and precise)
  - 4-8 decks: Use Monte Carlo with 10,000+ iterations
- **Player hand is frozen**: Calculations assume the player stands with their current hand
- **Dealer follows rules**: Simulations respect dealerHitsOnSoft17 and other game rules

#### Complete Game Scenario

```php
use Ecourty\PHPCasino\Blackjack\Model\{GameRules,Hand,Shoe};use Ecourty\PHPCasino\Blackjack\Service\{HandEvaluator,ProbabilityCalculator\MonteCarloProbabilityCalculator};

// Setup game with Vegas rules
$rules = GameRules::vegas();
$shoe = Shoe::fromGameRules($rules);
$shoe->shuffle();

$evaluator = new HandEvaluator();
$probabilityCalculator = ProbabilityCalculator::create();

// Deal initial cards
[$playerCard1, $playerCard2, $dealerUpCard, $dealerHoleCard] = $shoe->draw(4);

$playerHand = Hand::fromCards($playerCard1, $playerCard2);
$dealerHand = Hand::fromCards($dealerUpCard, $dealerHoleCard);

echo "=== BLACKJACK GAME ===\n\n";
echo "Player's Hand: {$playerCard1} {$playerCard2}\n";
echo "Player's Value: " . $evaluator->getHandValue($playerHand) . "\n";
echo "Player's Type: " . $evaluator->getHandType($playerHand)->getName() . "\n\n";

echo "Dealer's Up Card: {$dealerUpCard}\n";
echo "Dealer's Hole Card: [Hidden]\n\n";

// Check for blackjack
if ($evaluator->isBlackjack($playerHand)) {
    echo "PLAYER BLACKJACK! 🎉\n";
    $payout = 100 * $rules->blackjackPayout; // e.g., $150 for 3:2 on $100 bet
    echo "Payout: $" . $payout . "\n";
    exit;
}

// Calculate probabilities (player decides whether to hit or stand)
$knownCards = [$playerCard1, $playerCard2, $dealerUpCard];
$probabilities = $probabilityCalculator->calculate(
    $playerHand,
    $dealerUpCard,
    $knownCards,
    $shoe,
    $rules,
    ProbabilityCalculationMethod::MONTE_CARLO,
    10000
);

echo "=== PROBABILITY ANALYSIS ===\n";
echo "If you STAND:\n";
echo "  Win: " . number_format($probabilities->getWinProbabilityPercent(), 2) . "%\n";
echo "  Loss: " . number_format($probabilities->getLossProbabilityPercent(), 2) . "%\n";
echo "  Push: " . number_format($probabilities->getPushProbabilityPercent(), 2) . "%\n";
echo "  Expected Value: $" . number_format($probabilities->expectedValue * 100, 2) . " (per $100 bet)\n\n";

// Player action: Can double down?
if ($playerHand->canDoubleDown()) {
    echo "Double down available!\n";
}

// Player action: Can split?
if ($playerHand->canSplit()) {
    echo "Split available!\n";
}

// Simulate player stands and dealer plays
echo "\n=== DEALER PLAYS ===\n";
echo "Dealer's Hole Card: {$dealerHoleCard}\n";
echo "Dealer's Hand: {$dealerUpCard} {$dealerHoleCard}\n";
echo "Dealer's Value: " . $evaluator->getHandValue($dealerHand) . "\n";

while ($evaluator->shouldDealerHit($dealerHand, $rules)) {
    $nextCard = $shoe->draw(1)[0];
    $dealerHand->addCard($nextCard);
    echo "Dealer hits: {$nextCard}\n";
    echo "Dealer's Value: " . $evaluator->getHandValue($dealerHand) . "\n";
    
    if ($evaluator->isBust($dealerHand)) {
        echo "Dealer BUSTS!\n";
        break;
    }
}

// Determine winner
echo "\n=== RESULT ===\n";
$result = $evaluator->compare($playerHand, $dealerHand, $rules);
echo $result->getName() . "\n";

if ($result->isPlayerVictory()) {
    echo "You win! 🎉\n";
    $payout = 200; // $100 bet + $100 win
    echo "Payout: $" . $payout . "\n";
} elseif ($result->isPush()) {
    echo "Push (tie)\n";
    echo "Payout: $100 (bet returned)\n";
} else {
    echo "Dealer wins\n";
    echo "Loss: -$100\n";
}

// Check if shoe needs reshuffle
if ($shoe->needsReshuffle()) {
    echo "\n[Cut card reached - shoe will be reshuffled]\n";
}
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
│   ├── Roulette/
│   │   ├── Enum/         # RouletteNumber, RouletteType, BetType
│   │   ├── Model/        # Board, Bet, OddsResult
│   │   ├── Service/      # BetValidator, OddsCalculator
│   │   └── Exception/    # Domain exceptions
│   ├── Blackjack/
│   │   ├── Enum/         # ActionType, GameResult, HandType
│   │   ├── Model/        # Hand, Shoe, GameRules, ProbabilityResult
│   │   ├── Service/      # HandEvaluator, ExactProbabilityCalculator, MonteCarloProbabilityCalculator
│   │   └── Exception/    # Domain exceptions
│   └── Common/
│       ├── Enum/         # CardRank, CardSuit
│       └── Model/        # Card, Deck
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
- **Blackjack Module**: Shoe management, hand evaluation, probability calculation (74 tests, 145 assertions)

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
