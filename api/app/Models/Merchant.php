<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Merchant extends Eloquent
{
    protected $collection = 'Merchant';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'merchantName',
        'merchantShortName',
        'status',
    ];
}
