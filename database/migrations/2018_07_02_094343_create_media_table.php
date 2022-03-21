<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type',16);
            $table->string('mime_type',16);
            $table->string('extension',4);
            $table->integer('size');
            $table->string('path',200);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("ALTER TABLE media ADD data MEDIUMBLOB");
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('media');
    }
}
