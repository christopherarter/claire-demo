<?php

namespace App\Services\PartnerService;

use App\Models\Business;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

class PartnerRequestFactory
{
    protected string $stubBaseUrl = 'https://some-partner-website.com/clair-pay-item-sync*';

    protected string $validApiKey = 'CLAIR-ABC-123';

    /**
     * Extract the business ID from the url.
     */
    protected function businessId(Request $request): string
    {
        return str($request->url())
            ->after('clair-pay-item-sync/')
            ->before('?')
            ->trim('/')
            ->toString();
    }

    public function generateResponse(Request $request, Business $business): PromiseInterface
    {
        $isLastPage = data_get($request->data(), 'page', 1) >= 3;

        return Http::response([
            'payItems' => [
                $this->payItemFactory($business),
                $this->payItemFactory($business),
            ],
            'isLastPage' => $isLastPage,
        ], 200);
    }

    /**
     * Generate the response.
     */
    protected function stubPartnerApiResponse(Request $request): PromiseInterface
    {

        $business = Business::query()
            ->where('external_id', $this->businessId($request))
            ->first();

        if (! $business) {
            return Http::response(null, 404);
        }

        return $this->generateResponse($request, $business);

    }

    /**
     * API response for a single pay item.
     * return array<string, string|float>
     */
    protected function payItemFactory(Business $business): array
    {
        $user            = $business->users()->withPivot('external_id')->inRandomOrder()->first();
        $pivotExternalId = $user->pivot->external_id;

        return [
            'id'          => str()->uuid(),
            'employeeId'  => $pivotExternalId,
            'hoursWorked' => fake()->randomFloat(2, 1, 40),
            'payRate'     => fake()->randomFloat(2, 10, 100),
            'date'        => now()->subDays(rand(1, 7))->format('Y-m-d'),
        ];
    }

    /**
     * Handle the stubbed request.
     */
    public function handleStubRequest(Request $request): PromiseInterface
    {
        $header = data_get($request->header('x-api-key'), 0);

        if (! $header) {
            return Http::response('Unauthorized', 401);
        }

        if ($header !== $this->validApiKey) {
            return Http::response('Unauthorized', 403);
        }

        if (! $this->businessId($request)) {
            return Http::response('Business ID must be provided.', 400);
        }

        return $this->stubPartnerApiResponse($request);

    }
}
