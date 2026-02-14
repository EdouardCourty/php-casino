# AGENTS.md - Coding Guidelines for AI Agents

## 🎯 Core Concept

This library provides a comprehensive set of classes and utilities for simulating casino games including Poker, Roulette, and Blackjack. It enables developers to build game systems, test strategies, and implement casino game logic in PHP applications.

### Problem Solved

Developers building casino-related applications need reliable, tested implementations of common casino games. Creating these from scratch is time-consuming and error-prone, particularly when handling game rules, card mechanics, and betting systems.

### Solution

The php-casino library provides well-structured, reusable components for casino games with proper separation of concerns, making it easy to integrate casino game functionality into any PHP project.

---

## 🏗️ Architecture

### Overview

The library follows a modular, game-specific architecture with a shared common layer. Each game (Poker, Roulette, Blackjack) is isolated in its own namespace, while shared components live in the Common namespace.

### Main Components

- **`src/Common/`** - Shared components used across all games (e.g., Card, Deck, and other game-agnostic utilities)
- **`src/Poker/`** - Poker-specific game logic, rules, and utilities
- **`src/Roulette/`** - Roulette game implementation (to be developed)
- **`src/Blackjack/`** - Blackjack game implementation (to be developed)

Each game module typically contains:
- **`Model/`** - Domain entities and value objects
- **`Service/`** - Business logic and game rules
- **`Enum/`** - Type-safe enumerations
- **`Exception/`** - Game-specific exceptions

---

## 🚀 Typical Use Cases

- **Strategy Testing** - Simulate thousands of hands/spins to test betting strategies and calculate expected outcomes
- **Game Development** - Integrate casino game logic into web applications, mobile apps, or gaming platforms
- **Education & Training** - Build educational tools to teach probability, game theory, or casino mechanics
- **Prototyping** - Quickly prototype casino game features without implementing complex rule systems from scratch
- **Analytics** - Analyze game outcomes, probabilities, and player behavior patterns

---

## 💡 Design Patterns Used

- **Value Objects** - Immutable objects like Card, representing game concepts
- **Service Layer** - Business logic encapsulated in service classes
- **Strategy Pattern** - Different game rules and variations implemented as strategies
- **Enumeration Pattern** - Type-safe enums for suits, ranks, game states, etc.
- **Exception Hierarchy** - Domain-specific exceptions for clear error handling

---

## Project breakdown

### Current Implementation

**Poker Module** (`src/Poker/`)
- Poker-specific enumerations (Street, CardRank, CardSuit, HandRank, ActionType, etc.)
- Models: Card, Deck, Hand, Board, Action, PlayerRange, EquityResult
- Services: HandEvaluator, EquityCalculator (with enumeration and Monte Carlo methods)
- Board class manages community cards with automatic burn/draw from Deck
- Organized into Enum/, Model/, Service/, and Exception/ subdirectories

### Planned Modules

**Common Module** (`src/Common/`) - To be developed
- Shared Card and Deck classes
- Common utilities and interfaces used across all games

**Roulette Module** (`src/Roulette/`) - To be developed
- Roulette wheel mechanics
- Betting system and payout calculations
- Game state management

**Blackjack Module** (`src/Blackjack/`) - To be developed
- Card dealing and hand evaluation
- Dealer rules and player actions
- Bet management and game flow

**IMPORTANT**: This section should evolve with the project. When a new feature is created, updated or removed, this section should too.

## 🧪 Testing

This library should be covered by unit, integration and functional tests.
The tests are located in the `tests/{Unit|Integration|Functional}` folder.
Unit tests can use mocks or stubs if needed.

---

## Remarks & Guidelines

### General

- NEVER commit or push the git repository.
- When unsure about something, you MUST ask the user for clarification. Same goes it the user request is unclear.
- When facing a problem that has an easy "hacky" solution, and a more robust but more difficult to implement one, always choose the robust one:
    - Easy hacky fixes become technical debt, and can lead to issues down the road
    - Robust solutions means the project will remain serious and well-built.
- ALWAYS write tests for the important components. Better safe than sorry!
- Do NOT write ANY type documentation unless explicitly asked.
- Once a feature is complete, update the @README.md and @AGENTS.md accordingly.
- The @README.md file should consist of a project overview for end-users, not a technical explanation of the project. It should include:
    - Table of contents
    - Quick start / Installation
    - Core features
    - Configuration reference
    - Usage
    - Development / Contribution guidelines

### PHP Library Development

- This is a standalone PHP library meant to be re-used across different PHP projects.
- Architecture, naming, design, extensibility and ease of installation/integration should be key priorities.
- Keep dependencies minimal to maximize compatibility and ease of adoption.
- Follow PSR standards (PSR-4 for autoloading, PSR-12 for coding style).

## 📚 References

- **Source code**: `/src`
- **Tests**: `/tests`
- **README**: User documentation
- **Composer Package**: `ecourty/php-casino`
- **PHP Documentation**: https://www.php.net/manual/en/
- **PSR Standards**: https://www.php-fig.org/psr/
