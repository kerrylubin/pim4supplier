<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('attribute_mapping', function (Blueprint $table) {
        //     $table->increments('attribute_id');
        //     $table->integer('attribute_supplier_id');
        //     $table->integer('supplier_id');
        //     $table->timestamps();
        // });

        Schema::create('admin_attributes', function (Blueprint $table) {
            // $table->unsignedInteger('id');
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->string('type');
            $table->boolean('required');
            $table->boolean('unique');
            $table->timestamps();

            // $table->foreign('id')
            // ->references('attribute_id')
            // ->on('attribute_mapping')
            // ->onUpdate('cascade')
            // ->onDelete('cascade');

            // $table->primary('id');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_attributes');
        Schema::dropIfExists('attribute_mapping');

    }
}
