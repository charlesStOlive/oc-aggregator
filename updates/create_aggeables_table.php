<?php namespace Waka\Agg\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
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
            $table->string('column')->nullable();
            $table->integer('count')->nullable();
            $table->double('sum', 15, 2)->nullable();
            $table->text('data')->nullable();
            $table->date('ended_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('waka_agg_aggeables');
    }
}
