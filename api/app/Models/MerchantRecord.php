<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class MerchantRecord extends Eloquent
{
    protected $_collection = 'MerchantCollection_';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'merchantUserId',
        'merchantUsername',
        'lastMerchantAccountId',
        'records',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];

    public function scopeFromTable($query, $tableName)
    {
        $query->from($tableName);
    }

    /**
     * Set the table associated with the model.
     *
     * @param  string  $table
     * @return $this
     */
    public function setTable($table)
    {
        $this->table = $this->_collection . $table;

        return $this;
    }

    public function getUser($userId, $name, $merchantAccountId, $merchantAccountName)
    {

    }
}
