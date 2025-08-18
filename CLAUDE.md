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

# Alternative: Run tests using php artisan (works if pest command not available)
php artisan test
```

#### Missing Livewire Assertions Package

The project includes `christophrumpel/missing-livewire-assertions` for enhanced Livewire testing. Available assertions:

- `assertPropertyWired('email')` - Check if a property is wired to an HTML field
- `assertMethodWired('submit')` - Check if a method is wired to an HTML field
- `assertMethodWiredToForm('upload')` - Check if a method is wired to a form
- `assertMethodWiredToAction('mouseenter', 'enter')` - Check generic method wiring
- `assertMethodWiredToEvent('setValue', 'change')` - Check method wired to JS event
- `assertContainsLivewireComponent(Component::class)` - Check for nested Livewire components
- `assertContainsBladeComponent(Button::class)` - Check for Blade components
- `assertSeeBefore('first', 'second')` - Check string order

**Note:** During development, only run tests for the specific files being modified (e.g., `pt tests/Feature/Services/ImageServiceTest.php`). Do not run the full test suite or Pint unless explicitly requested by the user or when the user indicates the task is complete.

### Code Quality

```bash
# Format PHP code with Laravel Pint
./vendor/bin/pint

# Format only changed PHP files
./vendor/bin/pint --dirty

# Check PHP code formatting without fixing
./vendor/bin/pint --test

# Format frontend files with Prettier (JS, CSS, Blade, JSON)
npm run format

# Check frontend formatting without fixing
npm run format:check
```

### Important: Finalizing Changes

**When the user indicates a task is complete:**

1. Run the entire test suite: `php artisan test` or `pt`
2. Run Pint to ensure PHP code formatting: `./vendor/bin/pint`
3. Run Prettier to ensure frontend formatting: `npm run format`
4. Ask the user: "How many points should I include in the commit message?"
5. Generate a commit message with:
    - Subject line (50 characters or less)
    - The requested number of bullet points
6. Present the commit message for approval
7. When approved, stage all changes and commit: `git add -A && git commit`

This ensures:

- No regressions were introduced
- All tests pass with the new changes
- Code follows Laravel formatting standards
- Frontend files are consistently formatted
- The codebase remains stable and maintainable
- Changes are properly committed with clear messages

**Note:** This is the ONLY circumstance where committing is authorized. All other git operations remain read-only.

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

### Tailwind Color Consistency

**Always replace zinc or other base colors with stone** when copying Tailwind snippets or examples. The application uses stone as its base color palette throughout. Any references to zinc, gray, slate, or neutral should be replaced with stone equivalents (e.g., `bg-zinc-800` → `bg-stone-800`, `border-zinc-200` → `border-stone-200`).

**Exception for Flux vendor files:** Do not publish Flux vendor files just to change zinc colors. We accept zinc color variables in Flux vendor components because we override them in app.css. Only publish Flux vendor files when functionality changes are needed (e.g., the brand component was published to add custom name slot content).

### Documentation and Package Information

**Always check the Laravel MCP for package documentation first**, even when provided with external URLs. The MCP's `search-docs` tool provides:

- Version-specific documentation matching installed packages
- Accurate information for the exact versions in use
- Laravel-specific integration guidance

This applies to all Laravel ecosystem packages including Flux UI, Livewire, Filament, Pest, etc.

### Use Artisan Commands for File Creation

**Always use `php artisan make:` commands to create new files** instead of creating them manually:

- `php artisan make:livewire ComponentName` for Livewire components
- `php artisan make:test --pest TestName` for Pest tests
- `php artisan make:model ModelName` for models
- `php artisan make:migration migration_name` for migrations
- `php artisan make:controller ControllerName` for controllers
- `php artisan make:class ClassName` for generic PHP classes
- Pass `--no-interaction` to ensure commands work without user input

### Avoid Single-Use Private Methods

**Do not create private methods that are only called once.** Keep logic inline unless there's a compelling reason to extract it, such as:

- The method will likely be reused in the future
- The extracted code is complex enough that separation significantly improves readability
- The method encapsulates a distinct, testable piece of business logic

Prefer inline code for simple transformations, calculations, or one-time operations.

### Framework-First Approach

**Always prefer framework features over base PHP functionality.** This ensures consistency, leverages built-in optimizations, and maintains idiomatic code patterns.

### Livewire SPA Navigation

**Always use Livewire's SPA functionality for navigation.** This provides a seamless, fast user experience without full page reloads.

- Use `wire:navigate` on all internal links in Blade templates
- Use `$this->redirect('/path', navigate: true)` in Livewire components
- Use `return redirect('/path')` in PHP controllers (Livewire will intercept and handle as SPA navigation)
- This applies to all navigation: links, redirects, form submissions, etc.

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

### Eloquent Query Best Practices

**NEVER use raw SQL methods unless absolutely necessary.** Always prefer Eloquent's built-in query builder methods:

- **AVOID**: `selectRaw()`, `whereRaw()`, `orderByRaw()`, `havingRaw()`, `DB::raw()`
- **PREFER**: Standard Eloquent methods and scopes
- **EXCEPTION**: Only use raw methods when there's no Eloquent equivalent for complex database-specific operations

When you need complex queries:

1. First, check Laravel MCP documentation for the appropriate Eloquent method
2. Consider breaking complex queries into multiple simpler queries
3. Use query scopes to encapsulate complex logic
4. Only resort to raw SQL as a last resort, and document why it's necessary

#### Livewire-Specific Guidelines:

- Use Livewire's reactive properties and computed properties
- Leverage Livewire's built-in validation
- Use wire:model for two-way data binding

### Livewire Security: #[Locked] Attribute

**Err on the side of caution - use the #[Locked] attribute for any properties that should not be modified from the frontend:**

#### When to Use #[Locked]:

- Properties containing IDs that control access or actions (e.g., `$postId`, `$userId`)
- Configuration flags that should only be set server-side (e.g., `$isAdmin`, `$canDelete`)
- Properties that if tampered with could bypass authorization
- **Configuration properties** that control component behavior (e.g., `$showWatchButton`)
- **Eloquent models** - even though they have built-in protection, explicitly lock them for clarity

#### When NOT to Use #[Locked]:

- Form inputs and search fields meant for user interaction
- Properties bound with `wire:model` for two-way data binding
- Properties that users are explicitly meant to modify

#### Best Practice:

**When in doubt, lock it.** It's better to be overly cautious with security than to leave potential vulnerabilities. Properties can always be unlocked later if needed for functionality.

Example from EpisodeCard:

```php
#[Locked]
public Episode $episode;  // Locked even though models have protection

#[Locked]
public bool $showWatchButton = true;  // Locked to prevent tampering with UI logic
```

**Important:** Even with #[Locked], always implement proper authorization checks in your actions. The attribute prevents tampering but doesn't replace authorization.

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

**NEVER run git commands unless they are read-only.** The ONLY exception is during the finalize workflow (see "Important: Finalizing Changes" above) where, after user approval of the commit message, you are authorized to stage and commit changes using `git add -A && git commit`.

### Commit Message Guidelines

- Keep commit messages to 2-6 bullet points
- Use 2-3 points for small tasks
- Use 4-6 points for larger, more complex changes
- Each point should be concise and describe a specific change
- Focus on the most important changes rather than listing every detail
- **NEVER** add "Generated with Claude Code" or "Co-Authored-By" lines to commit messages

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.3.24
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- livewire/flux (FLUXUI_FREE) - v2
- livewire/flux-pro (FLUXUI_PRO) - v2
- livewire/livewire (LIVEWIRE) - v3
- laravel/pint (PINT) - v1
- pestphp/pest (PEST) - v3
- tailwindcss (TAILWINDCSS) - v4

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

## Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan

- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs

- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging

- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms

=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function \_\_construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments

- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks

- Add useful array shape type definitions for arrays when appropriate.

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

=== herd rules ===

## Laravel Herd

- The application is served by Laravel Herd and will be available at: https?://[kebab-case-project-dir].test. Use the `get-absolute-url` tool to generate URLs for the user to ensure valid URLs.
- You must not run any commands to make the site available via HTTP(s). It is _always_ available through Laravel Herd.

=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] <name>` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

## Laravel 12

- Use the `search-docs` tool to get version specific documentation.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

### Laravel 12 Structure

- No middleware files in `app/Http/Middleware/`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- **No app\Console\Kernel.php** - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== fluxui-free/core rules ===

## Flux UI Free

- This project is using the free edition of Flux UI. It has full access to the free components and variants, but does not have access to the Pro components.
- Flux UI is a component library for Livewire. Flux is a robust, hand-crafted, UI component library for your Livewire applications. It's built using Tailwind CSS and provides a set of components that are easy to use and customize.
- You should use Flux UI components when available.
- Fallback to standard Blade components if Flux is unavailable.
- If available, use Laravel Boost's `search-docs` tool to get the exact documentation and code snippets available for this project.
- Flux UI components look like this:

<code-snippet name="Flux UI Component Usage Example" lang="blade">
    <flux:button variant="primary"/>
</code-snippet>

### Available Components

This is correct as of Boost installation, but there may be additional components within the codebase.

<available-flux-components>
avatar, badge, brand, breadcrumbs, button, callout, checkbox, dropdown, field, heading, icon, input, modal, navbar, profile, radio, select, separator, switch, text, textarea, tooltip
</available-flux-components>

=== fluxui-pro/core rules ===

## Flux UI Pro

- This project is using the Pro version of Flux UI. It has full access to the free components and variants, as well as full access to the Pro components and variants.
- Flux UI is a component library for Livewire. Flux is a robust, hand-crafted, UI component library for your Livewire applications. It's built using Tailwind CSS and provides a set of components that are easy to use and customize.
- You should use Flux UI components when available.
- Fallback to standard Blade components if Flux is unavailable.
- If available, use Laravel Boost's `search-docs` tool to get the exact documentation and code snippets available for this project.
- Flux UI components look like this:

<code-snippet name="Flux UI component usage example" lang="blade">
    <flux:button variant="primary"/>
</code-snippet>

### Available Components

This is correct as of Boost installation, but there may be additional components within the codebase.

<available-flux-components>
accordion, autocomplete, avatar, badge, brand, breadcrumbs, button, calendar, callout, card, chart, checkbox, command, context, date-picker, dropdown, editor, field, heading, icon, input, modal, navbar, pagination, popover, profile, radio, select, separator, switch, table, tabs, text, textarea, toast, tooltip
</available-flux-components>

=== livewire/core rules ===

## Livewire Core

- Use the `search-docs` tool to find exact version specific documentation for how to write Livewire & Livewire tests.
- Use the `php artisan make:livewire [Posts\CreatePost]` artisan command to create new components
- State should live on the server, with the UI reflecting it.
- All Livewire requests hit the Laravel backend, they're like regular HTTP requests. Always validate form data, and run authorization checks in Livewire actions.

## Livewire Best Practices

- Livewire components require a single root element.
- Use `wire:loading` and `wire:dirty` for delightful loading states.
- Add `wire:key` in loops:

    ```blade
    @foreach ($items as $item)
        <div wire:key="item-{{ $item->id }}">
            {{ $item->name }}
        </div>
    @endforeach
    ```

- Prefer lifecycle hooks like `mount()`, `updatedFoo()`) for initialization and reactive side effects:

<code-snippet name="Lifecycle hook examples" lang="php">
    public function mount(User $user) { $this->user = $user; }
    public function updatedSearch() { $this->resetPage(); }
</code-snippet>

## Testing Livewire

<code-snippet name="Example Livewire component test" lang="php">
    Livewire::test(Counter::class)
        ->assertSet('count', 0)
        ->call('increment')
        ->assertSet('count', 1)
        ->assertSee(1)
        ->assertStatus(200);
</code-snippet>

    <code-snippet name="Testing a Livewire component exists within a page" lang="php">
        $this->get('/posts/create')
        ->assertSeeLivewire(CreatePost::class);
    </code-snippet>

=== livewire/v3 rules ===

## Livewire 3

### Key Changes From Livewire 2

- These things changed in Livewire 2, but may not have been updated in this application. Verify this application's setup to ensure you conform with application conventions.
    - Use `wire:model.live` for real-time updates, `wire:model` is now deferred by default.
    - Components now use the `App\Livewire` namespace (not `App\Http\Livewire`).
    - Use `$this->dispatch()` to dispatch events (not `emit` or `dispatchBrowserEvent`).
    - Use the `components.layouts.app` view as the typical layout path (not `layouts.app`).

### New Directives

- `wire:show`, `wire:transition`, `wire:cloak`, `wire:offline`, `wire:target` are available for use. Use the documentation to find usage examples.

### Alpine

- Alpine is now included with Livewire, don't manually include Alpine.js.
- Plugins included with Alpine: persist, intersect, collapse, and focus.

### Lifecycle Hooks

- You can listen for `livewire:init` to hook into Livewire initialization, and `fail.status === 419` for the page expiring:

<code-snippet name="livewire:load example" lang="js">
document.addEventListener('livewire:init', function () {
    Livewire.hook('request', ({ fail }) => {
        if (fail && fail.status === 419) {
            alert('Your session expired');
        }
    });

    Livewire.hook('message.failed', (message, component) => {
        console.error(message);
    });

});
</code-snippet>

=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.

=== pest/core rules ===

## Pest

### Testing

- If you need to verify a feature is working, write or update a Unit / Feature test.

### Pest Tests

- All tests must be written using Pest. Use `php artisan make:test --pest <name>`.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files - these are core to the application.
- Tests should test all of the happy paths, failure paths, and weird paths.
- Tests live in the `tests/Feature` and `tests/Unit` directories.
- Pest tests look and behave like this:
  <code-snippet name="Basic Pest Test Example" lang="php">
  it('is true', function () {
  expect(true)->toBeTrue();
  });
  </code-snippet>

### Running Tests

- Run the minimal number of tests using an appropriate filter before finalizing code edits.
- To run all tests: `php artisan test`.
- To run all tests in a file: `php artisan test tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --filter=testName` (recommended after making a change to a related file).
- When the tests relating to your changes are passing, ask the user if they would like to run the entire test suite to ensure everything is still passing.

### Pest Assertions

- When asserting status codes on a response, use the specific method like `assertForbidden` and `assertNotFound` instead of using `assertStatus(403)` or similar, e.g.:
  <code-snippet name="Pest Example Asserting postJson Response" lang="php">
  it('returns all', function () {
  $response = $this->postJson('/api/docs', []);

                $response->assertSuccessful();

    });
    </code-snippet>

### Mocking

- Mocking can be very helpful when appropriate.
- When mocking, you can use the `Pest\Laravel\mock` Pest function, but always import it via `use function Pest\Laravel\mock;` before using it. Alternatively, you can use `$this->mock()` if existing tests do.
- You can also create partial mocks using the same import or self method.

### Datasets

- Use datasets in Pest to simplify tests which have a lot of duplicated data. This is often the case when testing validation rules, so consider going with this solution when writing tests for validation rules.

<code-snippet name="Pest Dataset Example" lang="php">
it('has emails', function (string $email) {
    expect($email)->not->toBeEmpty();
})->with([
    'james' => 'james@laravel.com',
    'taylor' => 'taylor@laravel.com',
]);
</code-snippet>

=== tailwindcss/core rules ===

## Tailwind Core

- Use Tailwind CSS classes to style HTML, check and use existing tailwind conventions within the project before writing your own.
- Offer to extract repeated patterns into components that match the project's conventions (i.e. Blade, JSX, Vue, etc..)
- Think through class placement, order, priority, and defaults - remove redundant classes, add classes to parent or child carefully to limit repetition, group elements logically
- You can use the `search-docs` tool to get exact examples from the official documentation when needed.

### Spacing

- When listing items, use gap utilities for spacing, don't use margins.

              <code-snippet name="Valid Flex Gap Spacing Example" lang="html">
                  <div class="flex gap-8">
                      <div>Superior</div>
                      <div>Michigan</div>
                      <div>Erie</div>
                  </div>
              </code-snippet>

### Dark Mode

- If existing pages and components support dark mode, new pages and components must support dark mode in a similar way, typically using `dark:`.

=== tailwindcss/v4 rules ===

## Tailwind 4

- Always use Tailwind CSS v4 - do not use the deprecated utilities.
- `corePlugins` is not supported in Tailwind v4.
- In Tailwind v4, you import Tailwind using a regular CSS `@import` statement, not using the `@tailwind` directives used in v3:

<code-snippet name="Tailwind v4 Import Tailwind Diff" lang="diff"

- @tailwind base;
- @tailwind components;
- @tailwind utilities;

* @import "tailwindcss";
  </code-snippet>

### Replaced Utilities

- Tailwind v4 removed deprecated utilities. Do not use the deprecated option - use the replacement.
- Opacity values are still numeric.

| Deprecated | Replacement |
|------------+--------------|
| bg-opacity-_ | bg-black/_ |
| text-opacity-_ | text-black/_ |
| border-opacity-_ | border-black/_ |
| divide-opacity-_ | divide-black/_ |
| ring-opacity-_ | ring-black/_ |
| placeholder-opacity-_ | placeholder-black/_ |
| flex-shrink-_ | shrink-_ |
| flex-grow-_ | grow-_ |
| overflow-ellipsis | text-ellipsis |
| decoration-slice | box-decoration-slice |
| decoration-clone | box-decoration-clone |

=== tests rules ===

## Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test` with a specific filename or filter.
  </laravel-boost-guidelines>
