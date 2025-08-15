# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 11 application with Livewire 3 for tracking TV shows and episodes. The application integrates with the TV Maze API to fetch show information.

### Features
- Users can search and add TV shows to their dashboard
- Track watched/unwatched episodes per show
- View upcoming episodes in chronological order (Dashboard)
- View unwatched episodes by importance (NOT IMPLEMENTED)
- Automated daily updates via scheduled commands
- Artwork/images from FanArtTV with local caching

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
# Update show IDs from TVMaze (fetches new shows)
php artisan tvmaze:update-ids

# Update episodes for initialized shows
php artisan tvmaze:update-initialized-shows

# Fetch artwork from FanArtTV for shows with TVDB IDs
php artisan fanart:update-images
```

These commands run daily via Laravel's scheduler (defined in `routes/console.php`).

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
   - TV Maze API integration in `app/Services/TVMazeService`
   - FanArtTV API integration in `app/Services/FanArtTVService` 
   - Image processing service in `app/Services/ImageService`
   - Legacy services (`TMDBService`, `TVDBService`) exist but deprecated
   - Test fixtures for API responses in `tests/Fixtures/Http/`

3. **Model Relationships**
   - `Show` has many `Season` has many `Episode`
   - `Show`/`Season` morphMany `Image` (polymorphic for artwork)
   - Many-to-many: `User` ↔ `Show` (tracking)
   - Many-to-many: `User` ↔ `Episode` (watch status)
   - Models use `$unguarded = true` for mass assignment
   - `Show::mostPopularImage(ImageType)` returns best image by type

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

- Shows use `external_id` as route key (TV Maze ID)
- Lazy loading for Livewire components via `placeholder()` methods
- Season/episode initialization happens on first show view
- Images fetched from FanArtTV on show initialization
- Database transactions wrap bulk operations
- Test fixtures contain actual API response samples
- Authentication required for most pages

### Image System
- Images stored as relative paths (e.g., `tv/123/hdtvlogo/image.png`)
- `/images/art/{path}` endpoint serves cached images or fetches from FanArtTV
- Storage disk: `art` (in storage/art/, gitignored)
- Images cached locally with 1-year browser cache headers

### Missing Core Features
- **Unwatched episodes by importance** - Not implemented
- User preferences/settings system
- Show recommendations

## Coding Principles

### Avoid Single-Use Private Methods
**Do not create private methods that are only called once.** Keep logic inline unless there's a compelling reason to extract it, such as:
- The method will likely be reused in the future
- The extracted code is complex enough that separation significantly improves readability
- The method encapsulates a distinct, testable piece of business logic

Prefer inline code for simple transformations, calculations, or one-time operations.

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

### Commit Message Guidelines

- Keep commit messages to 2-6 bullet points
- Use 2-3 points for small tasks
- Use 4-6 points for larger, more complex changes
- Each point should be concise and describe a specific change
- Focus on the most important changes rather than listing every detail