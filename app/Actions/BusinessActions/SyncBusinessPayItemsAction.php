<?php

namespace App\Actions\BusinessActions;

use App\Models\Business;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\RequestInterface;

class SyncBusinessPayItemsAction
{
    public function client(): PendingRequest
    {
        return Http::withRequestMiddleware(
            function (RequestInterface $request) {
                return $request->withHeader('x-api-key', config('services.partner.api-key'));
            }
        )->baseUrl(config('services.partner.url'));

    }

    /**
     * Handle the action.
     *
     * @throws ConnectionException
     */
    public function handle(Business $business): void
    {
        try {

            $page       = 1;
            $isLastPage = false;

            while (! $isLastPage) {
                $response = $this->client()->get('/clair-pay-item-sync/'.$business->external_id, ['page' => $page]);

                if ($response->status() === 401) {
                    Log::alert('Unauthorized access to partner API. Check API key.');
                    throw new \Exception('Unauthorized access to partner API');
                }

                if ($response->status() === 404) {
                    Log::critical('Business not found in partner API.', ['business_id' => $business->id]);
                    throw new \Exception('Business not found in partner API');
                }

                // write a catch for 403
                if ($response->status() === 403) {
                    Log::alert('Forbidden access to partner API.');
                    throw new \Exception('Forbidden access to partner API');
                }

                $data       = $response->json();
                $payItems   = $data['payItems'];
                $isLastPage = $data['isLastPage'];

                foreach ($payItems as $item) {
                    $user = $business->users()->wherePivot('external_id', $item['employeeId'])->first();

                    if (! $user) {
                        continue;
                    }

                    $business->payItems()->updateOrCreate(
                        ['external_id' => $item['id']],
                        [
                            'user_id'  => $user->id,
                            'hours'    => $item['hoursWorked'],
                            'pay_rate' => $item['payRate'],
                            'paid_at'  => $item['date'],
                            'amount'   => $this->calculateAmount($business, $item['hoursWorked'], $item['payRate']),
                        ]
                    );
                }

                $page++;
            }

            // Remove pay items not present in the API response
            $business->payItems()->whereNotIn('external_id', collect($payItems)->pluck('id'))->delete();

        } catch (ConnectionException $e) {
            $message = $e->getCode().': Partner Connection error';

            Log::error($message, [
                'business_id'          => $business->id,
                'business_external_id' => $business->external_id,
                'message'              => $e->getMessage(),
            ]);

            // Let the exception bubble back up. Little catch and release.
            throw $e;
        }
    }

    /**
     * Calculate the amount for a pay item.
     */
    public function calculateAmount(Business $business, float $hoursWorked, float $payRate): float
    {
        $deductionPercent    = $business->deduction_percent ?? 3000; // Default to 30% if null
        $deductionMultiplier = (10000 - $deductionPercent) / 10000;

        $amount = $hoursWorked * $payRate * $deductionMultiplier;

        return round($amount, 2);
    }
}
