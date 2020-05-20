<?php namespace Waka\Agg\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class CreateAggregablesTable extends Migration
{
    public function up()
    {
        Schema::create('waka_agg_aggregables', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('aggregable_id')->unsigned();
            $table->string('aggregable_type');
            $table->integer('periodeable_id')->unsigned();
            $table->string('periodeable_type');
            $table->integer('nb')->nullable();
            $table->double('avg')->nullable();
            $table->double('amount')->nullable();
            $table->text('datas')->nullable();
            $table->date('start_at')->nullable();
            $table->date('end_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('waka_agg_aggregables');
    }
}
