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
            $table->timestamp('taken_at');
            $table->timestamp('ended_at');
            $table->integer('data_source_id')->unsigned();;
            $table->text('log');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('waka_agg_aggeable_logs');
    }
}
