<?php

namespace App\Models;

use Database\Factories\PayItemFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PayItem model
 *
 * @property int $id
 * @property int $user_id
 * @property int $business_id
 * @property int $amount
 * @property int $minutes
 * @property float $hours
 * @property \Illuminate\Support\Carbon $paid_at
 * @property string $external_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class PayItem extends Model
{
    /** @use HasFactory<PayItemFactory> */
    use HasFactory;

    /**
     * Guarded fields
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    /**
     * User of this pay item.
     *
     * @return BelongsTo<User, PayItem>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Business of this pay item.
     *
     * @return BelongsTo<Business, PayItem>
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Pay item hours.
     *
     * @return Attribute<float, never>
     */
    public function hours(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): float => round($attributes['minutes'] / 60, 2),
        );
    }
}
