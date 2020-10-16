<?php namespace Waka\Agg\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateAggeablesTable extends Migration
{
    public function up()
    {
        Schema::create('waka_agg_aggeables', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('aggeable_id')->unsigned();
            $table->string('aggeable_type');
            $table->string('type')->nullable();
            $table->integer('year')->nullable();
            $table->integer('num')->nullable();
            $table->string('agg')->nullable();
            $table->string('column')->nullable();
            $table->double('value')->nullable();
            $table->date('start_at')->nullable();
            $table->date('end_at')->nullable();
            $table->index(['aggeable_id', 'aggeable_type'], 'aggregable');
        });
    }

    public function down()
    {
        Schema::dropIfExists('waka_agg_aggeables');
    }
}
