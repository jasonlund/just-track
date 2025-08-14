# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 11 application with Livewire 3 for tracking TV shows and episodes. The application integrates with the TV Maze API to fetch show information.

### Features
- Allows users to add television shows to their dashboard
- Users can track which episodes of the television show have been watched
- Users can view a list of upcoming (and previously aired) episodes in chronological order
- Users can view a list of unwatched episodes sorted by order of importance
- Regularly scheduled commands keep the database of shows, episodes and air dates up to date

## Essential Commands

### Development
```bash
# Start development server
php artisan serve

# Start Vite development server for frontend assets
npm run dev

# Build frontend assets for production
npm run build

# Run database migrations
php artisan migrate

# Seed the database
php artisan db:seed
```

### Testing
```bash
# Run all tests
php artisan test

# Run tests with Pest (preferred test framework)
pest

# Run a specific test file
pest tests/Feature/Livewire/Pages/ShowShowTest.php

# Run tests with coverage
pest --coverage
```

**Note:** During development, only run tests for the specific files being modified (e.g., `pt tests/Feature/Services/ImageServiceTest.php`). Do not run the full test suite or Pint unless explicitly requested by the user or when the user indicates the task is complete.

### Code Quality
```bash
# Format code with Laravel Pint
./vendor/bin/pint

# Check code formatting without fixing
./vendor/bin/pint --test
```

### Important: Finalizing Changes
**When the user indicates a task is complete:**
1. Run the entire test suite: `php artisan test` or `pt`
2. Run Pint to ensure code formatting: `./vendor/bin/pint`

This ensures:
- No regressions were introduced
- All tests pass with the new changes
- Code follows Laravel formatting standards
- The codebase remains stable and maintainable

### Console Commands
```bash
# Update show IDs from TVMaze
php artisan tvmaze:update-ids
```

## Architecture Overview

### Stack
- **Backend**: Laravel 11 with PHP 8.2+
- **Frontend**: Livewire 3 for reactive components, Blade templates
- **Database**: SQLite (database/database.sqlite)
- **Testing**: Pest PHP with Laravel testing utilities
- **Asset Bundling**: Vite

### Key Architectural Patterns

1. **Livewire Components Structure**
   - Pages are full-page Livewire components in `app/Livewire/Pages/`
   - Reusable components in `app/Livewire/Components/`
   - Each component has a corresponding Blade view in `resources/views/livewire/`
   - Tests mirror the component structure in `tests/Feature/Livewire/`

2. **Service Layer Pattern**
   - TV Maze API integration is encapsulated in `app/Services/TVMazeService`
   - Service handles API communication and data transformation
   - Legacy service classes (`TMDBService`, `TVDBService`) exist but are not currently used
   - Test fixtures for API responses stored in `tests/Fixtures/Http/TVMaze/`

3. **Model Relationships**
   - `Show` has many `Season` has many `Episode`
   - Many-to-many relationships between `User` and `Show` (tracking)
   - Many-to-many relationships between `User` and `Episode` (watch status)
   - Models use `$unguarded = true` for mass assignment

4. **Authentication & User Context**
   - Uses Laravel's built-in authentication
   - User-specific show tracking via pivot tables
   - Computed attributes like `attached` on Show model for user context

5. **Testing Strategy**
   - Feature tests for Livewire components using `Livewire::test()`
   - Model tests for business logic
   - Service tests with mocked HTTP responses
   - Test helpers defined in `tests/Pest.php`
   - Factory pattern for test data generation
   - **Pest Best Practices:**
     - Use `and()` to chain multiple assertions on related data
     - Example: `expect($data)->toBeArray()->and($data['key'])->toBe('value')`
     - This keeps tests more readable and groups related assertions together
   
   **Arrange-Act-Assert (AAA) Pattern:**
   All tests should follow the AAA pattern for clarity and consistency:
   
   ```php
   it('performs some behavior', function () {
       // Arrange - Set up test data and initial conditions
       $user = User::factory()->create();
       $show = Show::factory()->create(['tvdb_id' => 12345]);
       
       // Act - Execute the behavior being tested
       $result = $this->service->fetchData($show);
       $processedData = $result->process();
       
       // Assert - Verify the expected outcomes
       expect($result)->toBeInstanceOf(Result::class)
           ->and($processedData)->toHaveCount(5)
           ->and($show->refresh()->status)->toBe('processed');
   });
   ```
   
   Guidelines:
   - **Always include clear comment markers** for each phase: `// Arrange`, `// Act`, `// Assert`
   - **STRICT ORDERING**: Tests must follow Arrange → Act → Assert order. Never place assertions before or between acts.
   - **Arrange phase**: Create all test data, set up mocks, configure initial state
   - **Act phase**: Execute the specific behavior/method being tested. Store ALL results in variables for later assertion.
   - **Assert phase**: Verify all expected outcomes using Pest's expect() syntax
   - **NO INTERLEAVING**: Never mix Act-Assert-Act-Assert patterns. Collect all action results first, then assert everything.
   - **For simple tests**, Act & Assert can be combined ONLY when using Livewire or HTTP testing methods that return testable responses
   - **Each test should focus on a single behavior** - if testing multiple behaviors, collect all results in Act, then assert all
   - **Keep phases visually separated** with the comment markers for better readability
   - **Cleanup/Teardown is rarely needed**: Laravel automatically clears cache (array driver) and sessions between tests. Only add cleanup for:
     - External file operations
     - Database connections outside of RefreshDatabase
     - External API resources
     - Use Pest's `afterEach()` hook when cleanup is truly necessary

## Important Implementation Details

- Shows use `external_id` as the route key instead of the primary key
- Lazy loading is used for Livewire components (see `placeholder()` methods)
- Season initialization happens on-demand in `ShowShow` component when first viewing a show
- Database transactions are used for bulk operations
- Test fixtures contain actual TV Maze API response samples for reliable testing
- Authentication required for most pages (enforced via middleware)

## Coding Principles

### Framework-First Approach
**Always prefer framework features over base PHP functionality.** This ensures consistency, leverages built-in optimizations, and maintains idiomatic code patterns.

#### Enum Usage
**ALWAYS use Enums instead of string literals when an Enum exists.** This provides type safety, IDE autocompletion, and prevents typos.
- Use `ImageType::TV_POSTER` instead of `'tvposter'`
- Method parameters should type-hint the Enum, not accept strings
- This ensures compile-time validation and better refactoring support

#### Laravel-Specific Guidelines:
- Use **Laravel Collections** instead of plain PHP arrays for data manipulation
  - `collect()` instead of array operations
  - `->isEmpty()` instead of `empty()`
  - `->isNotEmpty()` instead of `!empty()`
  - `->has()` instead of `isset()`
  - `->get()` instead of array access
  - `->count()` instead of `count()`
  - `->keys()` instead of `array_keys()`
- Use **Eloquent scopes** for reusable query logic (e.g., `scopeInitialized()`)
- Use **Laravel helpers** over PHP functions where available
  - `Str::` methods for string manipulation
  - `Arr::` methods for array operations
  - `now()` instead of `new DateTime()`
  - `storage_path()`, `base_path()`, etc. for paths
- Use **Eloquent relationships** and eager loading instead of manual joins
- Use **Form Requests** for validation instead of inline validation
- Use **Laravel's query builder** methods instead of raw SQL where possible

#### Livewire-Specific Guidelines:
- Use Livewire's reactive properties and computed properties
- Leverage Livewire's built-in validation
- Use wire:model for two-way data binding

This approach ensures the codebase remains maintainable, performant, and consistent with Laravel/Livewire best practices.

## Comment Guidelines

**Prefer self-documenting code over comments:**
- Code should be clear through descriptive naming and structure
- Comments should explain WHY not WHAT
- Good comments provide context about business logic or API limitations
- Remove comments that merely describe what the code does

### Examples of Good Comments:
- `// Cache search results for 3 hours to avoid hammering the API` - explains rationale
- `// If we get a 404, the page doesn't exist` - clarifies API behavior  
- `// TVMaze pagination is based on show ID, not fixed page size` - documents non-obvious API quirk

### Examples of Unnecessary Comments:
- `// Set test API key` - obvious from code
- `// Create a show` - code is self-explanatory
- `// Run the command` - redundant description

**AAA Section Comments in Tests**: Simple `// Arrange`, `// Act`, `// Assert` comments are acceptable for clarity but avoid verbose sub-comments like `// Act - First fetch` or `// Act & Assert - with empty query`

## Git Commands Policy

**NEVER run git commands unless they are read-only.** When asked for a git commit message, only provide the message text itself, not the git command. The user will handle the actual commit process.