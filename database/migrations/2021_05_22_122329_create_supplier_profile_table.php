<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplierProfileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('supplier_profile', function (Blueprint $table) {
        //     $table->id();
        //     $table->integer('supplier_id');
        //     $table->string('feed_url');
        //     $table->string('delimiter');
        //     $table->string('frequency');
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('supplier_profile');
    }
}
