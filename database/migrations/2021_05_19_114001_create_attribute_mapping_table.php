<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttributeMappingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attribute_mapping', function (Blueprint $table) {
            // $table->increments('attribute_id');
            $table->id('attribute_id');
            $table->integer('attribute_supplier_id');
            $table->integer('supplier_id');
            $table->string('attribute_label');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attribute_mapping');
    }
}
