<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('gender')->nullable();           
            $table->date('date_of_birth')->nullable();
            $table->string('company')->nullable();
            $table->string('phone_number')->nullable();
            $table->mediumText('address')->nullable();
            $table->string('avatar')->default('default.jpg');
            $table->boolean('verified')->default(0);
            $table->boolean('is_admin')->default(0);
            $table->integer('role_id')->default(0);
            $table->string('user_type')->default(null);
            $table->tinyInteger('enabled')->default(1);
            $table->integer('created_by');
            $table->string('branch_id')->nullable();
            $table->string('token', 254)->nullable();   // confirm_token', 100
            $table->rememberToken();
            $table->dateTime('notification_check')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });
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
