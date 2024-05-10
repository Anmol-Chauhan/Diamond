<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class  CreateMpauthorizenetCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mpauthorizenet_cards', function (Blueprint $table) {
            $table->increments('id');
            $table->foreign('customers_id')->references('id')->on('customers')->onDelete('cascade');
            $table->integer('customers_id')->nullable()->unsigned();
            $table->text('token');
            $table->string('last_four')->nullable();
            $table->json('misc')->nullable();
            $table->integer('is_default')->nullable();
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
        Schema::dropIfExists('mpauthorizenet_cards');
    }
}
