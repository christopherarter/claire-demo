<?php

use App\Actions\BusinessActions\SyncBusinessPayItemsAction;
use App\Jobs\SyncBusinessJob;
use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->seed();
});

test('partner api returns 401 when no api key provided', function () {
    $response = Http::get(config('services.partner.url'));
    expect($response->status())->toBe(401);
});

test('partner api returns 403 when wrong api key provided', function () {

    $response = Http::withHeaders([
        'x-api-key' => 'BAD-KEY',
    ])->get(config('services.partner.url'));
    expect($response->status())->toBe(403);
});

test('partner api returns 200 when correct api key provided', function () {

    $url = collect([
        config('services.partner.url'),
        '/clair-pay-item-sync/',
        Business::first()->external_id,
    ])->implode('');

    $response = app(SyncBusinessPayItemsAction::class)->client()->get($url);

    expect($response->status())->toBe(200);

});

test('SyncBusinessPayItemsAction successfully syncs pay items', function () {

    /**
     * Pretend we're mocking the partner api response
     */
    $business = Business::factory()->create();
    $user     = User::factory()->create();
    $business->users()->attach($user);

    $pivotExternalId = $business->users()->withPivot('external_id')->first()->pivot->external_id;

    $response = [
        'id'          => str()->uuid(),
        'employeeId'  => $pivotExternalId,
        'hoursWorked' => fake()->randomFloat(2, 1, 40),
        'payRate'     => fake()->randomFloat(2, 10, 100),
        'date'        => now()->subDays(rand(1, 7))->format('Y-m-d'),
    ];

    $url = config('services.partner.url').'/clair-pay-item-sync/'.Business::first()->external_id;

    Http::fake([$url => Http::response($response)]);

    SyncBusinessJob::dispatchSync($business);

    $this->assertDatabaseHas('pay_items', [
        'business_id' => $business->id,
        'user_id'     => $user->id,
    ]);

});

it('calculates amount correctly with various deduction percentages', function ($deductionPercent, $hoursWorked, $payRate, $expectedAmount) {
    $business                    = new Business;
    $business->deduction_percent = $deductionPercent;

    $action = new SyncBusinessPayItemsAction;
    $result = $action->calculateAmount($business, $hoursWorked, $payRate);

    expect(abs($result - $expectedAmount))->toBeLessThanOrEqual(0.01);
})->with([
    'default 30% deduction' => [null, 8.5, 12.5, 74.38],
    '50% deduction'         => [5000, 8.5, 12.5, 53.13],
    '0% deduction'          => [0, 10, 20, 200.00],
    '100% deduction'        => [10000, 5, 15, 0.00],
]);

it('uses default 30% deduction when deduction_percent is null', function () {
    $business                    = new Business;
    $business->deduction_percent = null;

    $action = new SyncBusinessPayItemsAction;
    $result = $action->calculateAmount($business, 10, 10);

    expect($result)->toBe(70.00);
});
