<?php

namespace App\Models;

use Database\Factories\BusinessFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Business model
 *
 * @property int $id
 * @property string $name
 * @property string $external_id
 * @property bool $enabled
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Business extends Model
{
    /** @use HasFactory<BusinessFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Return a the users that belong to the business.
     *
     * @return BelongsToMany<User>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(BusinessUser::class);
    }

    /**
     * Return the pay items that belong to the business.
     *
     * @return HasMany<PayItem>
     */
    public function payItems(): HasMany
    {
        return $this->hasMany(PayItem::class);
    }
}
