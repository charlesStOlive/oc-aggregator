<?php namespace Waka\Agg\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Schema;

class CreateAggeableLogsTable extends Migration
{
    public function up()
    {
        Schema::create('waka_agg_aggeable_logs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamp('taken_at');
            $table->timestamp('ended_at')->nullable();
            $table->string('data_source')->nullable();
            $table->integer('parts')->nullable();
            $table->integer('parts_ended')->nullable();
            $table->string('log')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('waka_agg_aggeable_logs');
    }
}
