<?php

use App\Livewire\Components\Show\ShowCard;
use Illuminate\Support\Str;
use Livewire\Livewire;

it('renders successfully', function () {
    $show = doctorWhoShowFactory()->create();

    Livewire::test(ShowCard::class, ['show' => $show])
        ->assertSet('show', $show)
        ->assertOk();
});

it("displays the show's information", function () {
    $show = doctorWhoShowFactory()->create();

    Livewire::test(ShowCard::class, ['show' => $show])
        ->assertSeeTextInOrder([
            'Doctor Who',
            'Started: 2005',
            'Status: Ended',
            '(2022)',
        ])
        ->assertSeeHtml('<p>Adventures across time and space with the time traveling alien and companions.</p>');
});

it('displays each status', function () {
    $statuses = ['running', 'ended', 'to be determined', 'in development'];

    $show = doctorWhoShowFactory()->create();

    foreach ($statuses as $status) {
        $show->status = $status;
        $show->save();

        Livewire::test(ShowCard::class, ['show' => $show])
            ->assertSeeText('Status: '.Str::title($status));
    }
});

it('displays each type', function () {
    $types = [
        'scripted', 'reality', 'animation', 'talk show', 'documentary', 'sports', 'variety', 'panel show', 'news',
        'game show', 'award show',
    ];

    $show = doctorWhoShowFactory()->create();

    foreach ($types as $type) {
        $show->type = $type;
        $show->save();

        Livewire::test(ShowCard::class, ['show' => $show])
            ->assertSeeText(Str::title($type));
    }
});

it('optionally displays the ended date', function () {
    $show = doctorWhoShowFactory()->create();

    $year = $show->ended->format('Y');

    Livewire::test(ShowCard::class, ['show' => $show])
        ->assertSeeText($year);

    $show->ended = null;
    $show->save();

    Livewire::test(ShowCard::class, ['show' => $show])
        ->assertDontSeeText($year);
});

it('optionally displays the premiered date', function () {
    $show = doctorWhoShowFactory()->create();

    $year = $show->premiered->format('Y');

    Livewire::test(ShowCard::class, ['show' => $show])
        ->assertSeeText($year);

    $show->premiered = null;
    $show->save();

    Livewire::test(ShowCard::class, ['show' => $show])
        ->assertDontSeeText($year)
        ->assertSeeText('Started: N/A');
});

it('optionally displays the summary', function () {
    $show = doctorWhoShowFactory([
        'summary' => 'foobar',
    ])->create();

    $summary = $show->summary;

    Livewire::test(ShowCard::class, ['show' => $show])
        ->assertSeeText($summary);

    $show->summary = null;
    $show->save();

    Livewire::test(ShowCard::class, ['show' => $show])
        ->assertDontSeeText($summary);
});
