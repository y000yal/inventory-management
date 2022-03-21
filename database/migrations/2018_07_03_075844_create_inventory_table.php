<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInventoryTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('inventory', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('serial', 128)->unique();
            $table->foreignId('vendor')->constrained('vendors')->onDelete('cascade');
            $table->foreignId('model')->constrained('models')->onDelete('cascade');
            $table->string('os_version', 16)->nullable();
            $table->foreignId('group_id')->nullable()->constrained('groups')->onDelete('cascade');
            $table->integer('batch_no')->nullable();
            $table->boolean('is_faulty')->default(0);
            $table->boolean('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('inventory');
    }
}
