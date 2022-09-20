<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShortcutKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shortcut_keys', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->longText('defaultShortcuts');
            $table->longText('customShortcuts')->nullable();
            $table->integer('created_by');
            $table->integer('shortcutsStatus');
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
        Schema::dropIfExists('shortcut_keys');
    }
}
