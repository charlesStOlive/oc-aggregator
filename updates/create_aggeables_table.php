<?php namespace Waka\Agg\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class CreateAggeablesTable extends Migration
{
    public function up()
    {
        Schema::create('waka_agg_aggeables', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('aggeable_id');
            $table->string('aggeable_type');
            $table->string('type')->nullable();
            $table->integer('year')->nullable();
            $table->integer('num')->nullable();
            $table->string('agg')->nullable();
            $table->string('column')->nullable();
            $table->double('value', 15, 2)->nullable();
            $table->date('ended_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('waka_agg_aggeables');
    }
}