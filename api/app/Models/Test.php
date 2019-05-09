<?php
namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Test extends Eloquent
{
    // protected $connection = 'mongodb';
    protected $collection = 'test_cc';

}
