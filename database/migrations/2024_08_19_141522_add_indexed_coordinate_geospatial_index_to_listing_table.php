<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexedCoordinateGeoSpatialIndexToListingTable extends Migration
{
    public function up()
    {
        Schema::table('listings', function (Blueprint $collection) {
            $collection->index(['indexedCoordinate' => '2dsphere']);
        });
    }

    public function down()
    {
        Schema::table('listings', function (Blueprint $collection) {
            $collection->dropIndex(['indexedCoordinate' => '2dsphere']);
        });
    }
}
