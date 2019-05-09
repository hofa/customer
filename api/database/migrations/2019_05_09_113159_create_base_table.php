<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Jenssegers\Mongodb\Schema\Blueprint;

class CreateBaseTable extends Migration
{
    protected $connection = 'mongodb';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection($this->connection)->
            table('GMAccount', function (Blueprint $collection) {
            $collection->index('id');
            $collection->string('name');
            $collection->unique('username');
            $collection->string('password', 64);
            $collection->timestamps();
        });
        Schema::connection($this->connection)->
            table('MerchantAccount', function (Blueprint $collection) {
            $collection->index('id');
            $collection->string('name');
            $collection->unique('username');
            $collection->string('password', 64);
            $collection->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection($this->connection)
            ->table('GMAccount', function (Blueprint $collection) {
                $collection->drop();
            });

        Schema::connection($this->connection)
            ->table('MerchantAccount', function (Blueprint $collection) {
                $collection->drop();
            });
    }
}
