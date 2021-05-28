<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplierUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_users', function (Blueprint $table) {
            $table->id();
            $table->string('bulk_action');
            $table->boolean('approved');
            $table->string('edit');
            $table->string('status');
            $table->string('sku');
            $table->string('product_name');
            $table->string('category');
            $table->string('price');
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
        Schema::dropIfExists('supplier_users');
    }
}
