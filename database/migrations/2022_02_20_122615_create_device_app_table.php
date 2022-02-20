<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeviceAppTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('device_app');
        Schema::create('device_app', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('device_id');
            $table->uuid('app_id');
            $table->string('client_token')->nullable();
            $table->tinyInteger('subscription')->default(0);
            $table->datetime('expire_date')->nullable();
            $table->string('operating_system')->nullable();
            $table->string('language')->default('en');
            $table->timestamps();
            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('device_app');
    }
}
