<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();

        });

        // Schema::create('suppliers', function (Blueprint $table) {
        //     $table->increments('id');
        //     $table->string('supplier_name');
        //     $table->timestamps();

        //     // $table->foreign('id')
        //     // ->references('id')
        //     // ->on('users')
        //     // ->onDelete('cascade')
        //     // ->onUpdate('cascade');

        //     // $table->primary('id');
        // });

        // Schema::create('users', function (Blueprint $table) {
        //     $table->unsignedInteger('id');
        //     $table->string('name');
        //     $table->string('email')->unique();
        //     $table->timestamp('email_verified_at')->nullable();
        //     $table->string('password');
        //     $table->rememberToken();
        //     $table->timestamps();

        //     $table->foreign('id')
        //     ->references('id')
        //     ->on('suppliers')
        //     ->onDelete('cascade')
        //     ->onUpdate('cascade');
        // });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');

    }
}
