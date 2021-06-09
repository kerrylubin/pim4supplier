<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplierDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->unsignedInteger('id');
            $table->string('supplier_name');
            $table->timestamps();

            $table->foreign('id')
            ->references('id')
            ->on('users')
            ->onDelete('cascade')
            ->onUpdate('cascade');

            $table->primary('id');
        });

        Schema::create('supplier_profile_users', function (Blueprint $table) {
            $table->unsignedInteger('id');
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->string('role');
            $table->timestamps();

            $table->foreign('id')
            ->references('id')
            ->on('suppliers')
            ->onDelete('cascade')
            ->onUpdate('cascade');

        });

        Schema::create('supplier_profile', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('supplier_id');
            $table->string('feed_url');
            $table->string('delimiter');
            $table->string('frequency');
            $table->timestamps();

            // $table->primary('id');

            $table->foreign('supplier_id')
            ->references('id')
            ->on('suppliers')
            ->onDelete('cascade')
            ->onUpdate('cascade');

        });

        Schema::create('supplier_attributes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('profile_id');
            $table->string('attribute_label');
            $table->timestamps();

            $table->foreign('profile_id')
            ->references('supplier_id')
            ->on('supplier_profile')
            ->onDelete('cascade')
            ->onUpdate('cascade');
        });

        Schema::create('supplier_import_profile', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('supplier_id');
            $table->string('feed_url');
            $table->string('delimiter');
            $table->string('frequency');
            $table->string('unique_code');
            $table->timestamps();

            // $table->primary('id');

            $table->foreign('supplier_id')
            ->references('id')
            ->on('suppliers')
            ->onDelete('cascade')
            ->onUpdate('cascade');

        });

        Schema::create('admin_attributes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code');
            $table->string('name');
            $table->string('type');
            $table->boolean('required');
            $table->boolean('unique');
            $table->timestamps();

        });

        Schema::create('attribute_mapping', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('admin_attribute_id');
            $table->unsignedInteger('supplier_attribute_id');
            $table->timestamps();
            // $table->primary('id');

            $table->foreign('admin_attribute_id')
            ->references('id')
            ->on('admin_attributes')
            ->onDelete('cascade')
            ->onUpdate('cascade');

            $table->foreign('supplier_attribute_id')
            ->references('id')
            ->on('supplier_attributes')
            ->onDelete('cascade')
            ->onUpdate('cascade');

        });

        Schema::create('attributes_values', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
            // $table->primary('id');

        });

        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('supplier_id');
            $table->string('unique_code');
            $table->timestamps();

            // $table->primary('id');

            $table->foreign('supplier_id')
            ->references('id')
            ->on('suppliers')
            ->onDelete('cascade')
            ->onUpdate('cascade');

        });

        Schema::create('product_atttributes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('mapping_id');
            $table->string('value');
            $table->timestamps();

            // $table->primary('id');

            $table->foreign('product_id')
            ->references('id')
            ->on('products')
            ->onDelete('cascade')
            ->onUpdate('cascade');

            $table->foreign('mapping_id')
            ->references('id')
            ->on('attribute_mapping')
            ->onDelete('cascade')
            ->onUpdate('cascade');

            $table->foreign('value')
            ->references('unique_code')
            ->on('products')
            ->onDelete('cascade')
            ->onUpdate('cascade');

        });

        Schema::create('products_attributes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('mapping_id');
            $table->unsignedInteger('attribute_values_id');
            $table->timestamps();
            // $table->primary('id');

            $table->foreign('product_id')
            ->references('id')
            ->on('products')
            ->onDelete('cascade')
            ->onUpdate('cascade');

            $table->foreign('mapping_id')
            ->references('id')
            ->on('attribute_mapping')
            ->onDelete('cascade')
            ->onUpdate('cascade');

            $table->foreign('attribute_values_id')
            ->references('id')
            ->on('attributes_values')
            ->onDelete('cascade')
            ->onUpdate('cascade');

        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('supplier_profile_users');
        Schema::dropIfExists('supplier_profile');
        Schema::dropIfExists('supplier_attributes');
        Schema::dropIfExists('supplier_import_profile');
        Schema::dropIfExists('products');
        Schema::dropIfExists('admin_attributes');
        Schema::dropIfExists('attribute_mapping');
        Schema::dropIfExists('attributes_values');
        Schema::dropIfExists('product_attributes');
        Schema::dropIfExists('products_attributes');

    }
}
