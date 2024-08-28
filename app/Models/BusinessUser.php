<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Str;

class BusinessUser extends Pivot
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $primaryKey = 'external_id';

    public function getKeyName()
    {
        return 'external_id';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = (string) Str::uuid();
        });
    }
}
