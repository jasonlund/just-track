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

### Code Quality
```bash
# Format code with Laravel Pint
./vendor/bin/pint

# Check code formatting without fixing
./vendor/bin/pint --test
```

### Important: Finalizing Changes
**Before finalizing any task or feature implementation:**
1. Run the entire test suite: `php artisan test`
2. Run Pint to ensure code formatting: `./vendor/bin/pint`

While you don't need to run the full suite after every small change during development, it's critical to run both the complete test suite and Pint before considering any task complete. This ensures:
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