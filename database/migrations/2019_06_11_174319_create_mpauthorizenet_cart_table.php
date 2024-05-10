<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMpauthorizenetCartTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mpauthorizenet_cart', function (Blueprint $table) {
            $table->increments('id');
            $table->foreign('cart_id')->references('id')->on('cart')->onDelete('cascade');
            $table->integer('cart_id')->unsigned();
            $table->json('mpauthorizenet_token');
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
        Schema::dropIfExists('mpauthorizenet_cart');
    }
}
