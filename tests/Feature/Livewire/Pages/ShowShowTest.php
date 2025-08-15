<?php

use App\Livewire\Components\Show\EpisodeList;
use App\Livewire\Components\Show\ShowCard;
use App\Livewire\Pages\ShowShow;
use App\Models\Season;
use App\Services\ImageService;
use Livewire\Livewire;

use function Pest\Laravel\get;

it('renders successfully', function () {
    asUser();

    $show = doctorWhoShowFactory()->create();

    get(route('show.show', [$show->external_id]))
        ->assertOk()
        ->assertSeeLivewire(ShowShow::class);

    Livewire::withoutLazyLoading()
        ->test(ShowShow::class, ['show' => $show])
        ->assertContainsLivewireComponent(ShowCard::class)
        ->assertContainsLivewireComponent(EpisodeList::class);
});

it("initializes a show's seasons that are not already", function () {
    asUser();

    $show = doctorWhoShowFactory()->create();

    Livewire::withoutLazyLoading()
        ->test(ShowShow::class, ['show' => $show])
        ->assertSee($show->name)
        ->assertSee('Started: '.$show->premiered?->format('Y'))
        ->assertSee('('.$show->ended->format('Y').')')
        ->assertSeeHtml($show->summary);

    expect($show->seasons()->count())
        ->toBe(13)
        ->and($show->seasons()->first())
        ->toMatchArray([
            'external_id' => 859,
            'number' => 1,
            'name' => 'Season 1',
            'image' => 'https://static.tvmaze.com/uploads/images/original_untouched/177/443002.jpg',
        ])

        ->and($show->seasons()->latest('number')->first()->name)
        ->toBe('Flux');

});

it('calls image service when initializing a show', function () {
    // Arrange
    asUser();
    $show = doctorWhoShowFactory()->create();
    
    $imageServiceMock = Mockery::mock(ImageService::class);
    $imageServiceMock->shouldReceive('fetchAndStoreShowImages')
        ->once()
        ->with(Mockery::on(fn ($arg) => $arg->id === $show->id))
        ->andReturn(0);
    
    $this->app->instance(ImageService::class, $imageServiceMock);
    
    // Act
    Livewire::withoutLazyLoading()
        ->test(ShowShow::class, ['show' => $show]);
    
    // Assert
    expect($show->refresh()->initialized)->toBeTrue();
});

it("does not initialize a show's seasons that is already initialized", function () {
    asUser();

    $show = doctorWhoShowFactory()
        ->has(Season::factory()->count(1))
        ->create(['initialized' => true]);

    Livewire::withoutLazyLoading()
        ->test(ShowShow::class, ['show' => $show]);

    expect($show->wasChanged())
        ->toBeFalse()

        ->and($show->seasons()->count())
        ->toBe(1);
});

it('does not call image service when show is already initialized', function () {
    // Arrange
    asUser();
    $show = doctorWhoShowFactory()
        ->has(Season::factory()->count(1))
        ->create(['initialized' => true]);
    
    $imageServiceMock = Mockery::mock(ImageService::class);
    $imageServiceMock->shouldNotReceive('fetchAndStoreShowImages');
    
    $this->app->instance(ImageService::class, $imageServiceMock);
    
    // Act
    Livewire::withoutLazyLoading()
        ->test(ShowShow::class, ['show' => $show]);
    
    // Assert
    expect($show->refresh()->initialized)->toBeTrue();
});

it('only shows existing shows', function () {
    asUser();

    get(route('show.show', ['show' => 'foobar']))
        ->assertNotFound();

    get(route('show.show', ['show' => 210]))
        ->assertNotFound();

    doctorWhoShowFactory()->create();

    get(route('show.show', ['show' => 210]))
        ->assertOk();
});

it('will optionally attach the show to a user', function () {
    $user = asUser();

    $show = doctorWhoShowFactory()
        ->has(Season::factory()->count(1))
        ->create(['initialized' => true]);

    expect($user->shows()->count())
        ->toBe(0);

    Livewire::withoutLazyLoading()
        ->test(ShowShow::class, [$show]);

    expect($user->shows()->count())
        ->toBe(0);

    Livewire::withoutLazyLoading()
        ->test(ShowShow::class, [$show, 'attach']);

    expect($user->shows()->count())
        ->toBe(1);
});

it('shows a show', function () {
    asUser();

    $show = doctorWhoShowFactory()
        ->has(Season::factory()->count(1))
        ->create(['initialized' => true]);

    Livewire::withoutLazyLoading()
        ->test(ShowShow::class, [$show])

        ->assertSee($show->name)
        ->assertSee($show->first_air_date)
        ->assertSee($show->origin_country)
        ->assertSee($show->overview);
});
