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
            // $table->id();
            $table->id('attribute_id');
            $table->integer('attribute_supplier_id');
            $table->integer('supplier_id');
            $table->timestamps();

            // $table->primary('attribute_id', 'attribute_mapping_attribute_id_primary');
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
