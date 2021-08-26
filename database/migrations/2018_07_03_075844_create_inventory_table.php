<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInventoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory', function (Blueprint $table) {
            $table->increments('id');
            $table->string('serial', 16)->unique();
            $table->unsignedInteger('vendor');
            $table->unsignedInteger('model');
            $table->string('os_version', 16);
            $table->unsignedInteger('group')->nullable();
            $table->integer('batch_no');
            $table->enum('status', ["active", "inactive", "faulty"])->default("inactive");
            $table->enum('web_user', ["0", "1"])->default("0");

            $table->timestamps();

            $table->foreign('model')->references('id')->on('models')->onDelete('cascade');
            $table->foreign('vendor')->references('id')->on('vendors')->onDelete('cascade');
            $table->foreign('group')->references('id')->on('groups')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory');
    }
}
