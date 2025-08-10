# FlashcardPro

Developer: Reza Khosravian  
Email: rezakhosravian45@gmail.com  
Framework: Laravel 12.x  
PHP: 8.2+

A full-featured flashcard application with authentication, deck/flashcard management, a Livewire-based study UI, and a secured REST API with OpenAPI documentation. Built for the Laravel Developer Applicant Challenge.

## 🚀 Features

- **Authentication & Authorization**: Registration/login, verified dashboard, policy-protected resources
- **Deck & Flashcard CRUD**: Organize cards into decks with full validation and resources
- **Study Mode (Livewire)**: Interactive session flow with progress and accuracy
- **Secured API**: Versioned under `/api/v1`, token-auth via Bearer or `X-API-Key`
- **OpenAPI Docs**: Auto-generated Swagger UI
- **Comprehensive Tests**: Unit, feature, integration, and Livewire component tests
- **Dockerized Dev**: One-command startup for reviewers

## 📋 Table of Contents

- Setup Instructions
- API Documentation
- Testing
- Challenge Alignment
- Architecture
- AI Tool Usage Disclosure
- Notes on Interesting Challenges

## ⚡ Setup Instructions

```bash
git clone <repo_url> flashcardpro
cd flashcardpro
cp .env.example .env

# Start containers (APP_PORT from .env controls the host port; defaults to 80)
docker compose up -d --build

# Run setup script (installs dependencies, runs migrations, seeds database, builds assets)
docker compose exec laravel.test bash deploy/dev.sh
# This script executes: composer install, npm install, php artisan migrate --seed, and more

# Open
# App: http://localhost:${APP_PORT:-80}
# API Docs: http://localhost:${APP_PORT:-80}/api/documentation
```

Seeded accounts (created by database seeders):
- john@flashcardpro.com / password
- jane@flashcardpro.com / password


## 📚 API Documentation

For the full list of APIs, open Swagger UI:

```bash
docker compose exec laravel.test php artisan l5-swagger:generate
open http://localhost:${APP_PORT:-80}/api/documentation
```
## 🧪 Test Suite Execution

Run the complete test suite:
```bash
docker compose exec laravel.test php artisan test
```

Run with coverage report:
```bash
docker compose exec laravel.test php artisan test --coverage
```

Coverage achieved: 90%+ across controllers, services, repositories, requests, resources, middleware, 
exceptions, and Livewire components.

## 🎯 Challenge Alignment

- Authentication: Laravel Breeze with verified routes and policies
- Deck/Flashcard: CRUD with Eloquent relations, DTOs, Requests, and Resources
- Study (Livewire): One-by-one reveal with progress and accuracy indicators
- API: Secured, versioned, standardized responses, Swagger docs
- DB: Migrations and seeders for users, decks, flashcards, API tokens
- Middleware: Custom `ApiKeyAuth` and `ValidateFlashcardBelongsToDeck`
- DI & Organization: Contracts, Services, Repositories, Queries, Providers
- Testing: Extensive unit/feature/integration coverage
- Security: Policies and strict authorization checks

Assumptions: SQLite for dev simplicity; port configurability via `APP_PORT`; study sessions size capped for UX/perf.

## 🏗 Architecture

Layered architecture with SOLID principles:

HTTP (Controllers, Requests, Middleware, Resources) → Services (business logic, DTOs, events) → Repositories/Queries (data access) → Domain (Models, Policies, Exceptions) → Infrastructure (Auth resolvers, utilities)

Key elements:

- Contracts in `app/Contracts/*`
- DTOs in `app/DTOs/*`
- Services in `app/Services/*`
- Repositories in `app/Repositories/*` with Queries in `app/Queries/*`
- Standardized responses via `ApiResponseService` and `ApiStatusCode`
- Auth via `ApiKeyAuth` and token resolver chain (Bearer, X-API-Key)


## 🤖 AI Tool Usage Disclosure

### Tools Utilized
- **Cursor AI** - Primary IDE with AI-powered code completion and refactoring
- **ChatGPT (GPT-4)** - Architecture consultation and complex problem-solving
- **GitHub Copilot** - Inline code suggestions and boilerplate generation

### Development Philosophy
I leveraged AI tools as productivity enhancers while maintaining full ownership and understanding of the codebase. Every AI-generated suggestion was critically evaluated, tested, and integrated thoughtfully into the application's architecture.

### Specific Use Cases

#### 1. Architecture & Configuration Management
- **When**: Initial project setup phase
- **Where**: `config/flashcard.php`, environment configuration, service providers
- **Why**: Establish configuration-driven development without hardcoded values
- **How**: Prompted for Laravel 12 best practices for configuration management
- **Example Prompt**: "Create a configuration file structure that centralizes all application constants using env() with sensible defaults, following Laravel conventions"
- **Result**: Clean, maintainable configuration system with environment flexibility

#### 2. Service Layer Implementation
- **When**: Building business logic layer
- **Where**: `app/Services/*`, `app/Contracts/Service/*`, DTOs
- **Why**: Implement clean architecture with proper separation of concerns
- **How**: Used AI to generate service interfaces and base implementations
- **Example Prompt**: "Generate a service layer for Deck management with DTOs for data transfer, following SOLID principles and repository pattern"
- **Result**: Type-safe, testable service layer with clear contracts

#### 3. API Standardization
- **When**: API development phase
- **Where**: `app/Services/Api/ApiResponseService.php`, all API controllers
- **Why**: Ensure consistent API responses across all endpoints
- **How**: Collaborated with AI to design response envelope structure
- **Example Prompt**: "Design a centralized API response service that handles success, error, and paginated responses with proper HTTP status codes and JSON:API-like structure"
- **Result**: Unified API response format with proper status codes and metadata

#### 4. Authentication System
- **When**: Implementing API security
- **Where**: `app/Http/Middleware/ApiKeyAuth.php`, `app/Infrastructure/Auth/*`
- **Why**: Support multiple authentication methods flexibly
- **How**: Used AI to implement Chain of Responsibility pattern
- **Example Prompt**: "Implement a flexible authentication system using Chain of Responsibility pattern that supports both Bearer tokens and API key headers, with easy extensibility for future methods"
- **Result**: Elegant, extensible authentication system

#### 5. Test Suite Development
- **When**: Throughout development (TDD approach)
- **Where**: All test files in `tests/*`
- **Why**: Achieve comprehensive test coverage (90%+)
- **How**: Generated test skeletons, then manually wrote assertions
- **Example Prompts**: 
  - "Generate PHPUnit test cases for DeckService covering all public methods, including edge cases and exception scenarios"
  - "Create feature tests for API endpoints with authentication, validation, and authorization scenarios"
- **Result**: Robust test suite with 66 test files covering all layers

#### 6. Livewire Components
- **When**: Building interactive UI
- **Where**: `app/Livewire/*`, study mode components
- **Why**: Create reactive, SPA-like experience
- **How**: Used AI for component structure and state management patterns
- **Example Prompt**: "Create a Livewire component for flashcard study sessions with card flipping, progress tracking, and keyboard navigation"
- **Result**: Smooth, interactive study experience

#### 7. Database Design & Optimization
- **When**: Data layer implementation
- **Where**: Migrations, models, queries
- **Why**: Ensure efficient data access patterns
- **How**: Consulted AI for relationship design and query optimization
- **Example Prompt**: "Review this Eloquent query for N+1 problems and suggest eager loading optimizations"
- **Result**: Optimized database queries with proper indexing

### Validation & Quality Assurance
- **Code Review**: Every AI suggestion underwent thorough manual review
- **Testing**: All AI-assisted code has corresponding test coverage
- **Refactoring**: AI suggestions were adapted to fit project patterns
- **Documentation**: AI helped generate initial documentation, manually refined

### Accountability Statement
I take full responsibility for all code in this submission. While AI tools significantly accelerated development (estimated 40% time savings), every line of code has been understood, validated, and can be explained in detail. The AI tools served as intelligent assistants, not decision-makers. All architectural decisions, business logic, and critical implementations were driven by my understanding of Laravel best practices and the specific requirements of this challenge.

## 📝 Notes on Interesting Challenges

The most engaging aspects of this project were:
- Implementing the chain-of-responsibility pattern for dual API authentication (Bearer + API Key)
- Building the interactive Livewire study mode with real-time card flipping and progress tracking
- Achieving 90%+ test coverage across all layers, particularly for Livewire components
- Structuring the application with clean architecture without over-engineering