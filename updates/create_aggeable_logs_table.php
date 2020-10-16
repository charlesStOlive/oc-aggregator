<?php namespace Waka\Agg\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateAggeableLogsTable extends Migration
{
    public function up()
    {
        Schema::create('waka_agg_aggeable_logs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamp('taken_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('data_source_id')->unsigned();
            $table->integer('parts')->unsigned()->nullable();
            $table->integer('parts_ended')->unsigned()->nullable();
            $table->text('log')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('waka_agg_aggeable_logs');
    }
}
