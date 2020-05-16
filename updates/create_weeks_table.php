<?php namespace Waka\Agg\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class CreateWeeksTable extends Migration
{
    public function up()
    {
        Schema::create('waka_agg_weeks', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->boolean('is_reday')->default(false);
            $table->integer('data_source_id')->unsigned();
            $table->integer('ag_week')->unsigned();
            $table->integer('ag_year')->unsigned();
            $table->integer('nb_l')->nullable();
            $table->integer('nb')->nullable();
            $table->double('avg')->nullable();
            $table->double('amount')->nullable();
            $table->text('datas')->nullable();
            $table->date('start_at');
            $table->date('end_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('waka_agg_weeks');
        Schema::dropIfExists('waka_agg_weekeable');
    }
}
