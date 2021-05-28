<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminFiltersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_filters', function (Blueprint $table) {
            $table->id();
            $table->boolean('approved');
            $table->string('status');
            $table->string('sku');
            $table->string('product_name');
            $table->string('category');
            $table->string('price');
            $table->string('supplier');
            $table->string('brand');
            $table->timestamps();
            $table->timestamp('last_synced_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_filters');
    }
}
